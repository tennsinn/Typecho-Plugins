<?php

class Bangumi_Action extends Typecho_Widget implements Widget_Interface_Do
{
	public function __construct($request, $response, $params = NULL)
	{
		parent::__construct($request, $response, $params);
		$this->_options = Helper::options();
		$this->_settings = Helper::options()->plugin('Bangumi');
		$this->_db = Typecho_Db::get();
	}

	public function action()
	{
		$this->widget("Widget_User")->pass("administrator");
		$this->on($this->request->is('do=epplus'))->epplus();
		$this->on($this->request->is('do=editSubject'))->editSubject();
		$this->on($this->request->is('do=editCollection'))->editCollection();
		$this->on($this->request->is('do=syncBangumi'))->syncBangumi();
	}

	/**
	 * 获取Bangumi对应账号收视信息
	 * @return void
	 */
	private function syncBangumi()
	{
		if($this->_settings->id)
		{
			$response = @file_get_contents('http://api.bgm.tv/user/'.$this->_settings->id.'/collection?cat=watching');
			$response = json_decode($response, true);
			if($response)
			{
				foreach($response as $bangumi)
				{
					$row = $this->_db->fetchRow($this->_db->select()->from('table.bangumi')->where('subject_id = ?', $bangumi['subject']['id']));
					if($row)
					{
						$subject = array('collection' => 'do', 'lasttouch' => $bangumi['lasttouch'], 'ep_status' => $bangumi['ep_status']);
						if($bangumi['eps'])
							$subject['eps'] = $bangumi['eps'];
						$this->_db->query($this->_db->update('table.bangumi')->rows($subject)->where('subject_id = ?', $bangumi['subject']['id']));
					}
					else
						$this->_db->query($this->_db->insert('table.bangumi')->rows(
							array(
								'subject_id' => $bangumi['subject']['id'],
								'type' => $bangumi['subject']['type'],
								'name' => $bangumi['subject']['name'],
								'name_cn' => $bangumi['subject']['name_cn'],
								'image' => $bangumi['subject']['images'][$this->_settings->image],
								'lasttouch' => $bangumi['lasttouch'],
								'collection' => 'do',
								'eps' => $bangumi['subject']['eps'],
								'ep_status' => $bangumi['ep_status']
							)
						));
				}
				$this->widget('Widget_Notice')->set(_t('成功获取Bangumi收视信息'), 'success');
			}
			else
				$this->widget('Widget_Notice')->set(_t('获取数据出错或无收视信息'), 'notice');
		}
		else
			$this->widget('Widget_Notice')->set(_t('未设置Bangumi用户id'), 'notice');
		$this->response->redirect(Typecho_Common::url('extending.php?panel=Bangumi%2FPanel.php&collection=do', $this->_options->adminUrl));
	}

	/**
	 * 收藏修改
	 * @return void
	 */
	private function editCollection()
	{
		if(isset($this->request->collection))
		{
			$collection = $this->request->get('collection');
			$subject_ids = $this->request->filter('int')->subject_id;
			if($subject_ids)
			{
				$subject_ids = is_array($subject_ids) ? $subject_ids : array($subject_ids);
				if($collection == 'delete')
				{
					foreach($subject_ids as $subject_id)
						$this->_db->query($this->_db->delete('table.bangumi')->where('subject_id = ?', $subject_id));
					$this->widget('Widget_Notice')->set(_t('已删除'.count($subject_ids).'条收藏记录'), 'success');
				}
				else
				{
					$failure = array();
					foreach($subject_ids as $subject_id)
					{
						$row = $this->_db->fetchRow($this->_db->select()->from('table.bangumi')->where('subject_id = ?', $subject_id));
						if($row)
						{
							$subject = array('collection' => $collection, 'lasttouch' => Typecho_Date::gmtTime());
							if($collection == 'collect')
								$subject['ep_status'] = $row['eps'];
							$this->_db->query($this->_db->update('table.bangumi')->rows($subject)->where('subject_id = ?', $subject_id));
						}
						else
						{
							$response = @file_get_contents('http://api.bgm.tv/subject/'.$subject_id.'?responseGroup=simple');
							$response = json_decode($response, true);
							if($response)
							{
								$subject = array(
									'subject_id' => $response['id'],
									'type' => $response['type'],
									'name' => $response['name'],
									'name_cn' => $response['name_cn'],
									'image' => $response['images'][$this->_settings->image],
									'lasttouch' => Typecho_Date::gmtTime(),
									'collection' => $collection,
									'eps' => $response['eps']
								);
								if($collection == 'collect')
									$subject['ep_status'] = $subject['eps'];
								$this->_db->query($this->_db->insert('table.bangumi')->rows($subject));
							}
							else
								array_push($failure, $subject_id);
						}
					}
					if($failure)
						$this->widget('Widget_Notice')->set(_t('以下记录修改失败：'.json_encode($failure)), 'notice');
					else
						$this->widget('Widget_Notice')->set(_t('已修改'.count($subject_ids).'条收藏记录'), 'success');
				}
			}
			else
				$this->widget('Widget_Notice')->set(_t('未选中任何项目'), 'notice');
		}
		else
			$this->widget('Widget_Notice')->set(_t('修改出错'), 'notice');
		$type = isset($this->request->type) ? $this->request->get('type') : '0';
		$this->response->redirect(Typecho_Common::url('extending.php?panel=Bangumi%2FPanel.php&type='.$type.'&collection='.($collection == 'delete' ? 'all' : $collection), $this->_options->adminUrl));
	}

	/**
	 * 记录信息编辑
	 * @return void
	 */
	private function editSubject()
	{
		if($this->request->get('subject_id') && isset($this->request->interest_rate) && isset($this->request->tags) && isset($this->request->comment) && isset($this->request->ep_status) && isset($this->request->eps))
		{
			if(!is_numeric($this->request->ep_status) || !is_numeric($this->request->eps) || $this->request->eps<0 || ($this->request->eps>0 && $this->request->ep_status>$this->request->eps))
				$this->response->throwJson(array('success' => false, 'message' => _t('请输入正确的收视进度')));
			elseif(!is_numeric($this->request->interest_rate) || $this->request->interest_rate>10 || $this->request->interest_rate<0)
				$this->response->throwJson(array('success' => false, 'message' => _t('评价请使用0-10的数字表示')));
			else
			{
				$subject = array(
					'interest_rate' => $this->request->interest_rate,
					'tags' => $this->request->tags,
					'comment' => $this->request->comment,
					'eps' => $this->request->eps
				);
				$json = array('status' => true, 'message' => _t('修改成功'));
				if($this->request->eps > 0 && $this->request->eps >= $this->request->ep_status)
					$subject['ep_status'] = $this->request->ep_status;
				if($this->request->eps > 0 && $this->request->eps == $this->request->ep_status)
				{
					$subject['collection'] = 'collect';
					$json['collection'] = 'collect';
				}
				else
					$json['collection'] = $this->request->collection;
				$this->_db->query($this->_db->update('table.bangumi')->where('subject_id = ?', $this->request->subject_id)->rows($subject));
				$this->response->throwJson($json);
			}
		}
		else
			$this->response->throwJson(array('success' => false, 'message' => _t('缺少必要信息')));
	}

	/**
	 * 收视进度增加
	 * @return void
	 */
	private function epplus()
	{
		if(!$this->request->get('subject_id'))
			$this->response->throwJson(array('status' => false, 'message' => _t('缺少必要信息')));
		$row = $this->_db->fetchRow($this->_db->select()->from('table.bangumi')->where('subject_id = ?', $this->request->subject_id));
		if($row['type'] != 2 && $row['type'] != 6)
			$this->response->throwJson(array('status' => false, 'message' => _t('所选记录无进度数据')));
		if(($row['ep_status']+1) < $row['eps'] || $row['eps'] == 0)
		{
			$this->_db->query($this->_db->update('table.bangumi')->expression('ep_status', 'ep_status + 1')->where('subject_id = ?', $this->request->subject_id));
			$this->response->throwJson(array('status' => true, 'collection' => 'do', 'ep_status' => ($row['ep_status']+1)));
		}
		else
		{
			$this->_db->query($this->_db->update('table.bangumi')->where('subject_id = ?', $this->request->subject_id)->rows(
				array(
					'ep_status' => $row['eps'],
					'collection' => 'collect'
				)
			));
			$this->response->throwJson(array('status' => true, 'collection' => 'collect', 'ep_status' => ($row['ep_status']+1)));
		}
	}

	/**
	 * 获取收藏条目
	 * @param  integer $pageSize 分页大小
	 * @return array
	 */
	public function getCollection($pageSize=10)
	{
		$collection = isset($this->request->collection) ? $this->request->get('collection') : 'all';
		$type = isset($this->request->type) ? $this->request->get('type') : '0';
		$query = $this->_db->select(array('COUNT(table.bangumi.id)' => 'num'))->from('table.bangumi');
		if($collection != 'all')
			$query->where('collection = ?', $collection);
		if($type != 0)
			$query->where('type = ?', $type);
		$num = $this->_db->fetchObject($query)->num;
		if(!$num)
			return array('status' => false, 'message' => '存在0条记录');
		$page = isset($this->request->page) ? $this->request->get('page') : 1;
		$query = $this->_db->select()->from('table.bangumi')->order('lasttouch', Typecho_Db::SORT_DESC)->page($page, $pageSize);
		if($collection != 'all')
			$query->where('collection = ?', $collection);
		if($type != 0)
			$query->where('type = ?', $type);
		$rows = $this->_db->fetchAll($query);
		$query = $this->request->makeUriByRequest('page={page}');
		$nav = new Typecho_Widget_Helper_PageNavigator_Box($num, $page, $pageSize, $query);
		return array('status' => true, 'list' => $rows, 'nav' => $nav);
	}

	/**
	 * Bangumi搜索
	 * @param  integer $pageSize 分页大小
	 * @return array
	 */
	public function search($pageSize=10)
	{
		if(!isset($this->request->keywords))
			return array('status' => false, 'message' => '请输入关键字');
		$page = isset($this->request->page) ? $this->request->get('page') : 1;
		$type = isset($this->request->type) ? $this->request->get('type') : '0';
		$keywords = $this->request->get('keywords');
		$response = @file_get_contents('http://api.bgm.tv/search/subject/'.$keywords.'?responseGroup=large&max_results='.$pageSize.'&start='.($page-1)*$pageSize.'&type='.$type);
		$response = json_decode($response, true);
		if(!$response || (isset($response['list']) && !$response['list']))
			return array('status' => false, 'message' => '搜索到0个结果');
		elseif(!isset($response['results']) && isset($response['error']))
			return array('status' => false, 'message' => '关键字：'.$keywords.' 搜索出现错误 '.$response['code'].':'.$response['error']);
		else
		{
			$query = $this->request->makeUriByRequest('page={page}');
			$nav = new Typecho_Widget_Helper_PageNavigator_Box($response['results'], $page, $pageSize, $query);
			return array('status' => true, 'list' => $response['list'], 'nav' => $nav);
		}
	}
}

?>