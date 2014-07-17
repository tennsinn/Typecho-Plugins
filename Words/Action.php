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
	
	public function addWords()
	{
		$content = $this->request->get('content');
		if($content)
		{
			$this->_db->query($this->_db->insert('table.words_contents')->rows(
				array(
					'created' => Typecho_Date::gmtTime(),
					'expression' => $this->request->get('expression'),
					'content' => $content,
				)
			));
			$this->widget('Widget_Notice')->set(_t('碎语添加成功'), 'success');
		}
		else
		{
			$this->widget('Widget_Notice')->set(_t('心情碎语内容不能为空'), 'notice');
		}
		$this->response->goBack();
	}
	
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

	public function addComments()
	{
		$content = $this->request->get('content');
		$author = $this->request->get('author');
		$wid = $this->request->get('wid');
		$parent = $this->request->get('parent');
		if($content && $author && $wid)
		{
			$this->_db->query($this->_db->insert('table.words_comments')->rows(
				array(
					'wid' => $wid,
					'created' => Typecho_Date::gmtTime(),
					'author' => $author,
					'mail' => $this->request->get('mail'),
					'url' => $this->request->get('url'),
					'ip' => $this->request->getIp(),
					'agent' => $this->request->getAgent(),
					'content' => $content,
					'parent' => $parent
					)
			));
			$this->_db->query($this->_db->update('table.words_contents')->expression('commentsNum', 'commentsNum + 1')->where('wid = ?', $wid));
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
}

?>