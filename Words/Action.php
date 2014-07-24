<?php

class Words_Action extends Typecho_Widget implements Widget_Interface_Do
{
	private $_db;
	private $_options;
	private $_settings;

	public function __construct($request, $response, $params = NULL)
	{
		parent::__construct($request, $response, $params);
		$this->_db = Typecho_Db::get();
		$this->_options = Helper::options();
		$this->_settings = Helper::options()->plugin('Words');
	}

	public function action()
	{
		$this->on($this->request->is('do=addWords'))->addWords();
		$this->on($this->request->is('do=delWords'))->delWords();
		$this->on($this->request->is('do=editWords'))->editWords();
		$this->on($this->request->is('do=addComments'))->addComments();
		$this->on($this->request->is('do=delComments'))->delComments();
	}
	
	/**
	 * 发表心情碎语
	 * 
	 * @return void
	 */
	public function addWords()
	{
		$content = $this->request->get('content');
		if($content)
		{
			$newWord = array(
				'created' => Typecho_Date::gmtTime(),
				'expression' => $this->request->get('expression'),
				'content' => $content
			);
			$this->_db->query($this->_db->insert('table.words_contents')->rows($newWord));
			$this->pluginHandle()->finishWord($newWord, $this);
			$this->widget('Widget_Notice')->set(_t('碎语添加成功'), 'success');
		}
		else
		{
			$this->widget('Widget_Notice')->set(_t('心情碎语内容不能为空'), 'notice');
		}
		$this->response->goBack();
	}
	
	/**
	 * 删除心情碎语及其评论
	 * 
	 * @return void
	 */
	public function delWords()
	{
		$wids = $this->request->filter('int')->wid;
		$delCount = 0;
		if($wids)
		{
			$wids = is_array($wids) ? $wids : array($wids);
			foreach($wids as $wid)
			{
				$this->_db->query($this->_db->delete('table.words_contents')->where('wid = ?', $wid));
				$this->_db->query($this->_db->delete('table.words_comments')->where('wid = ?', $wid));
				$delCount++;
			}
		}
		$this->widget('Widget_Notice')->set($delCount > 0 ? _t('已删除'.$delCount.'条碎语') : _t('未选中任何碎语'), $delCount > 0 ? 'success' : 'notice');
		$this->response->redirect(Typecho_Common::url('extending.php?panel=Words%2FPanel.php', $this->_options->adminUrl));
	}

	/**
	 * 编辑心情碎语
	 * 
	 * @return void
	 */
	public function editWords()
	{
		$wid = $this->request->get('wid');
		$content = $this->request->get('content');
		if($content)
		{
			$expression = $this->request->get('expression');
			$this->_db->query($this->_db->update('table.words_contents')->rows(
				array(
					'expression' => $expression,
					'content' => $content,
				)
			)->where('wid = ?', $wid));
			$this->widget('Widget_Notice')->set(_t('碎语添加成功'), 'success');
		}
		else
		{
			$this->widget('Widget_Notice')->set(_t('心情碎语内容不能为空'), 'notice');
		}
		$this->response->goBack();
	}

	/**
	 * 添加碎语评论
	 * 
	 * @return void
	 */
	public function addComments()
	{
		$content = $this->request->get('content');
		$author = $this->request->get('author');
		$wid = $this->request->get('wid');
		$parent = $this->request->get('parent');
		if($content && $author && $wid)
		{
			$newComment = array(
				'wid' => $wid,
				'created' => Typecho_Date::gmtTime(),
				'author' => $author,
				'mail' => $this->request->get('mail'),
				'url' => $this->request->get('url'),
				'ip' => $this->request->getIp(),
				'agent' => $this->request->getAgent(),
				'content' => $content,
				'parent' => $parent
			);
			$this->_db->query($this->_db->insert('table.words_comments')->rows($newComment));
			$this->_db->query($this->_db->update('table.words_contents')->expression('commentsNum', 'commentsNum + 1')->where('wid = ?', $wid));
			$this->pluginHandle()->finishComment($newComment, $this);
			if($parent && in_array('reviewer', $this->_settings->notice))
			{
				$rowNew = $this->_db->fetchRow($this->_db->select()->from('table.words_comments')->order('cid', Typecho_Db::SORT_DESC));
				$rowParent = $this->_db->fetchRow($this->_db->select()->from('table.words_comments')->where('cid = ?', $parent));
				if($rowParent['mail'])
					$this->_funcSendMail($rowNew, $rowParent);
			}
			if(!$parent && in_array('admin', $this->_settings->notice))
			{
				$rowNew = $this->_db->fetchRow($this->_db->select()->from('table.words_comments')->order('cid', Typecho_Db::SORT_DESC));
				$rowParent = $this->_db->fetchRow($this->_db->select()->from('table.words_contents')->where('wid = ?', $wid));
				$this->_funcSendMail($rowNew, $rowParent);
			}
		}
		$this->response->goBack();
	}

	/**
	 * 递归删除相关评论
	 * 
	 * @param  integer $cid 评论cid值
	 * @return integer 删除计数
	 */
	private function _funcDelete($cid)
	{
		$delCount = 0;
		$row = $this->_db->fetchRow($this->_db->select()->from('table.words_comments')->where('cid = ?', $cid));
		$this->_db->query($this->_db->delete('table.words_comments')->where('cid = ?', $cid));
		$this->_db->query($this->_db->update('table.words_contents')->expression('commentsNum', 'commentsNum - 1')->where('wid = ?', $row['wid']));
		$delCount++;
		$rows = $this->_db->fetchAll($this->_db->select()->from('table.words_comments')->where('parent = ?', $cid));
		foreach($rows as $row)
		{
			$delCount += $this->_funcDelete($row['cid']);
		}
		return $delCount;
	}

	/**
	 * 邮件发送
	 * 
	 * @param  array $rowNew	新插入的信息
	 * @param  array $rowParent	相关的上一条信息
	 * @return void
	 */
	private function _funcSendMail($rowNew, $rowParent)
	{
		require_once 'phpmailer/PHPMailerAutoload.php';
		$mail = new PHPMailer();
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';
		$mail->isSMTP();
		$mail->isHTML(true); 
		if($this->_settings->auth)
			$mail->SMTPAuth = true;
		if($this->_settings->ssl)
			$mail->SMTPSecure = 'ssl';
		$mail->Username = $this->_settings->username;
		$mail->Password = $this->_settings->password;
		$mail->Host = $this->_settings->host;
		$mail->Port = $this->_settings->port;
		$mail->setFrom($this->_settings->username, $this->_options->title);
		if($rowNew['parent'])
		{
			$mail->addAddress($rowParent['mail'], $rowParent['author']);
			$subject = empty($this->_settings->titileReviewer) ? '{site}：您在心情碎语的评论有了回复' : $this->_settings->titileReviewer;
			$template = file_get_contents('.'. __TYPECHO_PLUGIN_DIR__.'/Words/phpmailer/reviewer.html');
		}
		else
		{
			$mail->addAddress($this->_settings->address);
			$subject = empty($this->_settings->titleAdmin) ? '{site}：心情碎语有了新的评论' : $this->_settings->titleAdmin;
			$template = file_get_contents('.'. __TYPECHO_PLUGIN_DIR__.'/Words/phpmailer/admin.html');
		}
		$search = array('{site}', '{siteUrl}', '{wid}', '{cid}', '{time}', '{author}', '{comment}', '{pcid}', '{ptime}', '{pcomment}');
		if($this->_settings->page)
		{
			$rowNew['wid'] = '<a href="'.Typecho_Common::url($this->_settings->page.'#words-'.$rowNew['wid'], $this->_options->index).'">#'.$rowNew['wid'].'</a>';
			$rowNew['cid'] = '<a href="'.Typecho_Common::url($this->_settings->page.'#words-comments-'.$rowNew['cid'], $this->_options->index).'">#'.$rowNew['cid'].'</a>';
			$rowParent['cid'] = '<a href="'.Typecho_Common::url($this->_settings->page.'#words-comments-'.$rowParent['cid'], $this->_options->index).'">#'.$rowParent['cid'].'</a>';
			if($rowNew['mail'])
				$rowNew['author'] = '<a href="mailto:'.$rowNew['mail'].'">'.$rowNew['author'].'</a>';
		}
		$siteUrl = '<a href="'.$this->_options->siteUrl.'">'.$this->_options->title.'</a>';
		$replace = array($this->_options->title, $siteUrl, $rowNew['wid'], $rowNew['cid'], date('Y-m-d H:i', $rowNew['created']+$this->_options->timezone), $rowNew['author'], $rowNew['content'], $rowParent['cid'], date('Y-m-d H:i', $rowParent['created']+$this->_options->timezone), $rowParent['content']);
		$mail->Subject = str_replace($search, $replace, $subject);
		$mail->Body = str_replace($search, $replace, $template);
		$result = $mail->send();
		if(in_array('log',$this->_settings->notice))
		{
			$fileLog = @fopen('.'. __TYPECHO_PLUGIN_DIR__.'/Words/phpmailer/log.txt','a+');
			$message = date('Y-m-d H:i', Typecho_Date::gmtTime()+$this->_options->timezone).': ';
			if($result)
				$message .= '发送成功'."\r\n";
			else
				$message .= $mail->ErrorInfo."\r\n";
			fwrite($fileLog, $message);
			fclose($fileLog);
		}
	}

	/**
	 * 删除评论及其回复评论
	 * 
	 * @return void
	 */
	public function delComments()
	{
		$delCount = 0;
		$cids = $this->request->filter('int')->cid;
		$cids = is_array($cids) ? $cids : array($cids);
		foreach($cids as $cid)
		{
			$delCount += $this->_funcDelete($cid);
		}
		$this->widget('Widget_Notice')->set($delCount > 0 ? _t('已删除'.$delCount.'条评论') : _t('未删除任何评论'), $delCount > 0 ? 'success' : 'notice');
		$this->response->redirect(Typecho_Common::url('extending.php?panel=Words%2FPanel.php&type=comments', $this->_options->adminUrl));
	}

	/**
	 * 获取碎语
	 * 
	 * @param  string $type 获取模式
	 * @param  integer $pageSize 分页条数
	 * @return array
	 */
	public function getWords($type='page', $pageSize=10)
	{
		if($type == 'page')
		{
			if(isset($this->request->wid))
			{
				$num = $this->_db->fetchObject($this->_db->select(array('COUNT(table.words_contents.wid)' => 'num'))->from('table.words_contents')->where('wid > ?', $this->request->get('wid')))->num;
				$page = ceil(($num+1)/$pageSize);
			}
			else
			{
				$page = isset($this->request->page) ? $this->request->get('page') : '1';
			}
			$rows = $this->_db->fetchAll($this->_db->select()->from('table.words_contents')->order('wid', Typecho_Db::SORT_DESC)->page($page, $pageSize));
			$query = $this->request->makeUriByRequest('page={page}');
			$num = $this->_db->fetchObject($this->_db->select(array('COUNT(table.words_contents.wid)' => 'num'))->from('table.words_contents'))->num;
			$nav = new Typecho_Widget_Helper_PageNavigator_Box($num, $page, $pageSize, $query);
			return array('words' => $rows, 'nav' => $nav);
		}
		elseif($type == 'latest')
		{
			$row = $this->_db->fetchRow($this->_db->select()->from('table.words_contents')->order('wid', Typecho_Db::SORT_DESC));
			return $row;
		}
		elseif($type == 'single')
		{
			$wid = $this->request->get('wid');
			$row = $this->_db->fetchRow($this->_db->select()->from('table.words_contents')->where('wid = ?', $wid));
			return $row;
		}
		else
		{
			$rows = $this->_db->fetchAll($this->_db->select()->from('table.words_contents')->order('wid', Typecho_Db::SORT_DESC));
			return $rows;
		}
	}

	/**
	 * 获取碎语评论
	 * 
	 * @param  string $type 获取模式
	 * @param  integer $wid 碎语wid值
	 * @param  integer $cid 评论cid值
	 * @param  integer $pageSize 分页条数
	 * @return array
	 */
	public function getComments($type='page', $wid=NULL, $cid=NULL, $pageSize=10)
	{
		if($type == 'page')
		{
			$page = isset($this->request->page) ? $this->request->get('page') : '1';
			if(isset($this->request->wid))
			{
				$rows = $this->_db->fetchAll($this->_db->select()->from('table.words_comments')->order('cid', Typecho_Db::SORT_DESC)->page($page, $pageSize)->where('wid = ?', $this->request->get('wid')));
				$num = $this->_db->fetchObject($this->_db->select(array('COUNT(table.words_comments.cid)' => 'num'))->from('table.words_comments')->where('wid = ?', $this->request->get('wid')))->num;
			}
			else
			{
				$rows = $this->_db->fetchAll($this->_db->select()->from('table.words_comments')->order('cid', Typecho_Db::SORT_DESC)->page($page, $pageSize));
				$num = $this->_db->fetchObject($this->_db->select(array('COUNT(table.words_comments.cid)' => 'num'))->from('table.words_comments'))->num;
			}
			$query = $this->request->makeUriByRequest('page={page}');
			
			$nav = new Typecho_Widget_Helper_PageNavigator_Box($num, $page, $pageSize, $query);
			return array('comments' => $rows, 'nav' => $nav);
		}
		elseif($type == 'related')
		{
			$rows = $this->_db->fetchAll($this->_db->select()->from('table.words_comments')->order('cid', Typecho_Db::SORT_ASC)->where('wid = ?', $wid));
			return $rows;
		}
		elseif($type == 'single')
		{
			$row = $this->_db->fetchRow($this->_db->select()->from('table.words_comments')->where('cid = ?', $cid));
			return $row;
		}
		else
		{
			$rows = $this->_db->fetchAll($this->_db->select()->from('table.words_comments')->order('cid', Typecho_Db::SORT_DESC));
			return $rows;
		}
	}

	/**
	 * 碎语添加编辑表单
	 * 
	 * @return void
	 */
	public function wordsWrite()
	{
		$type = isset($this->request->type) ? $this->request->get('type') : 'words';
		if($type == 'edit'):
			$word = $this->getWords('single');
	?>
			<h3>编辑碎语 <span>#<?php echo $word['wid']; ?></span></h3>
			<form method="post" name="edit_words" action="<?php $this->_options->index('/action/words?do=editWords') ?>">
				<input type="hidden" name="wid" value="<?php echo $word['wid']; ?>" required>
	<?php
		else:
	?>
			<h3>新增碎语</h3>
			<form method="post" name="add_words" action="<?php $this->_options->index('/action/words?do=addWords') ?>">
	<?php
		endif;
	?>
				<table width="100%">
					<colgroup>
						<col width="60px">
						<col width="">
						<col width="100px">
					</colgroup>
					<thead style="text-align:left;">
						<th>表情</th>
						<th>碎语</th>
						<th></th>
					</thead>
					<tbody style="text-align:center;"><tr>
						<td><select name="expression" id="dropdown_expression" class="egg_imagedropdown">
					<?php
						for($i=1; $i<90; $i++)
						{
							echo '<option';
							if(isset($this->request->wid) && 'onion-'.$i.'.gif' == $word['expression'])
								echo ' selected';
							echo ' value="onion-'.$i.'.gif">';
							echo $this->_options->pluginUrl('/Words/expression/onion-'.$i.'.gif');
							echo '</option>';
						}
					?>
						</select></td>
						<td><textarea style="width:100%;" name="content"><?php echo isset($this->request->wid) ? $word['content'] : ''; ?></textarea></td>
						<td><button class="primary" type="submit">添加</button></td>
					</tr></tbody>
				</table>
	<?php 
		$this->pluginHandle()->writeOption($this);
	?>
			</form>
	<?php
	}
}

?>