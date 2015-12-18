<?php

class Collection_Action extends Typecho_Widget implements Widget_Interface_Do
{
	public function __construct($request, $response, $params = NULL)
	{
		parent::__construct($request, $response, $params);
		$this->_options = Helper::options();
		$this->_settings = Helper::options()->plugin('Collection');
		$this->_db = Typecho_Db::get();
	}

	public function action()
	{
		$this->widget("Widget_User")->pass("administrator");
		$this->on($this->request->is('do=plusEp'))->plusEp();
		$this->on($this->request->is('do=editSubject'))->editSubject();
		$this->on($this->request->is('do=editStatus'))->editStatus();
		$this->on($this->request->is('do=getBangumi'))->getBangumi();
	}

	/**
	 * 获取Bangumi对应账号收视信息
	 *
	 * @return void
	 */
	private function getBangumi()
	{
		if($this->_settings->uid)
		{
			$response = @file_get_contents('http://api.bgm.tv/user/'.$this->_settings->uid.'/collection?cat=watching');
			$response = json_decode($response, true);
			if($response)
			{
				foreach($response as $bangumi)
				{
					$row_temp = $this->_db->fetchRow($this->_db->select()->from('table.collection')->where('bangumi_id = ?', $bangumi['subject']['id']));
					if($row_temp)
					{
						
						$row = array(
							'status' => 'do', 
							'time_touch' => $bangumi['lasttouch'], 
							'ep_status' => $bangumi['ep_status']
						);
						if($bangumi['subject']['eps'] && !$row_temp['ep_count'])
							$row['ep_count'] = $bangumi['subject']['eps'];
						$this->_db->query($this->_db->update('table.collection')->rows($row)->where('bangumi_id = ?', $bangumi['subject']['id']));
					}
					else
					{
						$row = array(
							'class' => $bangumi['subject']['type'],
							'name' => $bangumi['subject']['name'],
							'name_cn' => $bangumi['subject']['name_cn'],
							'image' => substr($bangumi['subject']['images']['common'], 31),
							'bangumi_id' => $bangumi['subject']['id'],
							'status' => 'do',
							'time_start' => Typecho_Date::gmtTime(),
							'time_touch' => $bangumi['lasttouch']
						);
						if($bangumi['ep_status'])
						{
							$row['ep_status'] = $bangumi['ep_status'];
							$row['ep_count'] = $bangumi['subject']['eps'];
						}
						else
							if($bangumi['subject']['eps'])
							{
								$row['ep_count'] = $bangumi['subject']['eps'];
								$row['ep_status'] = 0;
							}
						$this->_db->query($this->_db->insert('table.collection')->rows($row));
					}
				}
				$this->widget('Widget_Notice')->set(_t('成功获取Bangumi收视信息'), 'success');
			}
			else
				$this->widget('Widget_Notice')->set(_t('获取数据出错或无收视信息'), 'notice');
		}
		else
			$this->widget('Widget_Notice')->set(_t('未设置Bangumi用户uid'), 'notice');
		$this->response->redirect(Typecho_Common::url('extending.php?panel=Collection%2FPanel.php&status=do', $this->_options->adminUrl));
	}

	/**
	 * 收藏修改
	 *
	 * @return void
	 */
	private function editStatus()
	{
		if(isset($this->request->status))
		{
			$status = $this->request->get('status');
			if(isset($this->request->id) && $ids = $this->request->filter('int')->getArray('id'))
			{
				if($status == 'delete')
				{
					foreach($ids as $id)
						$this->_db->query($this->_db->delete('table.collection')->where('id = ?', $id));
					$this->widget('Widget_Notice')->set(_t('已删除'.count($ids).'条收藏记录'), 'success');
				}
				else
				{
					foreach($ids as $id)
					{
						$row = array('status' => $status, 'time_touch' => Typecho_Date::gmtTime());
						$row_temp = $this->_db->fetchRow($this->_db->select()->from('table.collection')->where('id = ?', $id));
						switch($status)
						{
							case 'do':
								if(!$row_temp['time_start'])
									$row['time_start'] = Typecho_Date::gmtTime();
								if($row_temp['time_finish'])
									$row['time_finish'] = NULL;
								break;
							case 'collect':
								if($row_temp['ep_count'])
									$row['ep_status'] = $row_temp['ep_count'];
							case 'dropped':
								$row['time_finish'] = Typecho_Date::gmtTime();
								break;
						}
						$this->_db->query($this->_db->update('table.collection')->rows($row)->where('id = ?', $id));
					}
				}
			}
			elseif(isset($this->request->subject_id) && $subject_ids = $this->request->filter('int')->getArray('subject_id'))
			{
				$failure = array();
				foreach($subject_ids as $subject_id)
				{
					$row_temp = $this->_db->fetchRow($this->_db->select()->from('table.collection')->where('bangumi_id = ?', $subject_id));
					if($row_temp)
					{
						$row = array(
							'status' => $status,
							'time_touch' => Typecho_Date::gmtTime()
						);
						switch($status)
						{
							case 'do':
								if(!$row_temp['time_start'])
									$row['time_start'] = Typecho_Date::gmtTime();
								if($row_temp['time_finish'])
									$row['time_finish'] = NULL;
								break;
							case 'collect':
								if($row_temp['ep_count'])
									$row['ep_status'] = $row_temp['ep_count'];
							case 'dropped':
								$row['time_finish'] = Typecho_Date::gmtTime();
								break;
						}
						$this->_db->query($this->_db->update('table.collection')->where('bangumi_id = ?', $subject_id)->rows($row));
					}
					else
					{
						$response = @file_get_contents('http://api.bgm.tv/subject/'.$subject_id.'?responseGroup=simple');
						$response = json_decode($response, true);
						if($response)
						{
							$row = array(
								'class' => $response['type'],
								'name' => $response['name'],
								'name_cn' => $response['name_cn'],
								'image' => substr($response['images']['common'], 31),
								'status' => $status,
								'time_touch' => Typecho_Date::gmtTime(),
								'bangumi_id' => $response['id']
							);
							if($response['eps'])
							{
								$row['ep_count'] = $response['eps'];
								$row['ep_status'] = 0;
							}
							switch($status)
							{
								case 'do':
									$row['time_start'] = Typecho_Date::gmtTime();
									break;
								case 'collect':
									$row['ep_status'] = $row['ep_count'];
								case 'dropped':
									$row['time_finish'] = Typecho_Date::gmtTime();
									break;
							}
							$this->_db->query($this->_db->insert('table.collection')->rows($row));
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
			else
				$this->widget('Widget_Notice')->set(_t('未选中任何项目'), 'notice');
		}
		else
			$this->widget('Widget_Notice')->set(_t('未指明收藏状态'), 'notice');
		$class = isset($this->request->class) ? $this->request->get('class') : '0';
		$this->response->redirect(Typecho_Common::url('extending.php?panel=Collection%2FPanel.php&class='.$class.'&status='.($status == 'delete' ? 'all' : $status), $this->_options->adminUrl));
	}

	/**
	 * 记录信息编辑
	 *
	 * @return void
	 */
	private function editSubject()
	{
		if($this->request->get('id') && isset($this->request->name))
		{
			if($this->request->name == '')
				$this->response->throwJson(array('success' => false, 'message' => _t('必须输入名称')));
			if( !is_numeric($this->request->class) && $this->request->class <= 0)
				$this->response->throwJson(array('success' => false, 'message' => _t('必须输入名称')));
			if((!is_null($this->request->get('ep_status')) || !is_null($this->request->get('ep_count'))) && (!is_numeric($this->request->ep_status) || !is_numeric($this->request->ep_count) || $this->request->ep_status<0 || $this->request->ep_count<0 || ($this->request->ep_count>0 && $this->request->ep_status>$this->request->ep_count)))
				$this->response->throwJson(array('success' => false, 'message' => _t('请输入正确的本篇进度')));
			if((!is_null($this->request->get('sp_status')) || !is_null($this->request->get('sp_count'))) && (!is_numeric($this->request->sp_status) || !is_numeric($this->request->sp_count) || $this->request->sp_status<0 || $this->request->sp_count<0 || ($this->request->sp_count>0 && $this->request->sp_status>$this->request->sp_count)))
				$this->response->throwJson(array('success' => false, 'message' => _t('请输入正确的特典进度')));
			if($this->request->get('rate') && (!is_numeric($this->request->rate) || $this->request->rate>10 || $this->request->rate<0))
				$this->response->throwJson(array('success' => false, 'message' => _t('评价请使用0-10的数字表示')));
			{
				$row = array(
					'class' => $this->request->class,
					'type' => $this->request->type,
					'name' => $this->request->name, 
					'name_cn' => $this->request->name_cn,
					'image' => $this->request->image,
					'ep_count' => $this->request->ep_count,
					'sp_count' => $this->request->sp_count,
					'time_touch' => Typecho_Date::gmtTime(),
					'ep_status' => $this->request->ep_status,
					'sp_status' => $this->request->sp_status,
					'rate' => $this->request->rate,
					'tags' => $this->request->tags,
					'comment' => $this->request->comment
				);
				$json = array('result' => true, 'message' => _t('修改成功'));
				if(($this->request->ep_count > 0 && $this->request->ep_count == $this->request->ep_status) && ($this->request->sp_count == 0 || ($this->request->sp_count > 0 && $this->request->sp_count == $this->request->sp_status)))
				{
					$row['status'] = 'collect';
					$json['status'] = 'collect';
				}
				else
					$json['status'] = $this->request->status;
				$this->_db->query($this->_db->update('table.collection')->where('id = ?', $this->request->id)->rows($row));
				$this->response->throwJson($json);
			}
		}
		else
			$this->response->throwJson(array('success' => false, 'message' => _t('缺少必要信息')));
	}

	/**
	 * 进度增加
	 *
	 * @return void
	 */
	private function plusEp()
	{
		if(!$this->request->get('id') || ($this->request->get('plus') != 'ep' && $this->request->get('plus') != 'sp'))
			$this->response->throwJson(array('result' => false, 'message' => _t('缺少必要信息')));
		$row = $this->_db->fetchRow($this->_db->select()->from('table.collection')->where('id = ?', $this->request->id));
		//if($row['class'] != 1 && $row['class'] != 2 && $row['class'] != 6)
		//	$this->response->throwJson(array('result' => false, 'message' => _t('所选记录无进度数据')));
		if($this->request->plus == 'ep')
		{
			if(($row['ep_status']+1) < $row['ep_count'] || $row['ep_count'] == '0')
			{
				$this->_db->query($this->_db->update('table.collection')->rows(array('ep_status' => ($row['ep_status']+1), 'time_touch' => Typecho_Date::gmtTime()))->where('id = ?', $this->request->id));
				$this->response->throwJson(array('result' => true, 'status' => 'do', 'plus' => 'ep', 'ep_status' => ($row['ep_status']+1)));
			}
			elseif($row['sp_status'] != $row['sp_count'])
			{
				$this->_db->query($this->_db->update('table.collection')->rows(array('ep_status' => $row['ep_count'], 'time_touch' => Typecho_Date::gmtTime()))->where('id = ?', $this->request->id));
				$this->response->throwJson(array('result' => true, 'status' => 'do', 'plus' => 'ep', 'ep_status' => $row['ep_count']));
			}
			else
			{
				$this->_db->query($this->_db->update('table.collection')->rows(array('status' => 'collect', 'ep_status' => $row['ep_count'], 'time_finish' => Typecho_Date::gmtTime(), 'time_touch' => Typecho_Date::gmtTime()))->where('id = ?', $this->request->id));
				$this->response->throwJson(array('result' => true, 'status' => 'collect', 'plus' => 'ep', 'ep_status' => $row['ep_count']));
			}
		}
		else
		{
			if(($row['sp_status']+1) < $row['sp_count'] || $row['sp_count'] == '0')
			{
				$this->_db->query($this->_db->update('table.collection')->rows(array('sp_status' => ($row['sp_status']+1), 'time_touch' => Typecho_Date::gmtTime()))->where('id = ?', $this->request->id));
				$this->response->throwJson(array('result' => true, 'status' => 'do', 'plus' => 'sp', 'sp_status' => ($row['sp_status']+1)));
			}
			elseif($row['ep_status'] != $row['ep_count'])
			{
				$this->_db->query($this->_db->update('table.collection')->rows(array('sp_status' => $row['sp_count'], 'time_touch' => Typecho_Date::gmtTime()))->where('id = ?', $this->request->id));
				$this->response->throwJson(array('result' => true, 'status' => 'do', 'plus' => 'sp', 'sp_status' => $row['sp_count']));
			}
			else
			{
				$this->_db->query($this->_db->update('table.collection')->rows(array('status' => 'collect', 'sp_status' => $row['sp_count'], 'time_finish' => Typecho_Date::gmtTime(), 'time_touch' => Typecho_Date::gmtTime()))->where('id = ?', $this->request->id));
				$this->response->throwJson(array('result' => true, 'status' => 'collect', 'plus' => 'sp', 'sp_status' => $row['sp_count']));
			}
		}
	}

	/**
	 * 获取收藏条目
	 *
	 * @param  integer $pageSize 分页大小
	 * @return array
	 */
	public function getCollection($pageSize=20)
	{
		$status = isset($this->request->status) ? $this->request->get('status') : 'do';
		$class = isset($this->request->class) ? $this->request->get('class') : '0';
		$query = $this->_db->select(array('COUNT(table.collection.id)' => 'num'))->from('table.collection');
		if($status != 'all')
			$query->where('status = ?', $status);
		if($class != 0)
			$query->where('class = ?', $class);
		$num = $this->_db->fetchObject($query)->num;
		if(!$num)
			return array('result' => false, 'message' => '存在0条记录');
		$page = isset($this->request->page) ? $this->request->get('page') : 1;
		$query = $this->_db->select()->from('table.collection')->order('time_touch', Typecho_Db::SORT_DESC)->page($page, $pageSize);
		if($status != 'all')
			$query->where('status = ?', $status);
		if($class != 0)
			$query->where('class = ?', $class);
		$rows = $this->_db->fetchAll($query);
		$query = $this->request->makeUriByRequest('page={page}');
		$nav = new Typecho_Widget_Helper_PageNavigator_Box($num, $page, $pageSize, $query);
		return array('result' => true, 'list' => $rows, 'nav' => $nav);
	}

	/**
	 * Bangumi搜索
	 *
	 * @param  integer $pageSize 分页大小
	 * @return array
	 */
	public function search($pageSize=10)
	{
		if(!isset($this->request->keywords))
			return array('result' => false, 'message' => '请输入关键字');
		$page = isset($this->request->page) ? $this->request->get('page') : 1;
		$class = isset($this->request->class) ? $this->request->get('class') : '0';
		$keywords = $this->request->get('keywords');
		$response = @file_get_contents('http://api.bgm.tv/search/subject/'.$keywords.'?responseGroup=large&max_results='.$pageSize.'&start='.($page-1)*$pageSize.'&type='.$class);
		$response = json_decode($response, true);
		if(!$response || (isset($response['list']) && !$response['list']))
			return array('result' => false, 'message' => '搜索到0个结果');
		elseif(!isset($response['results']) && isset($response['error']))
			return array('result' => false, 'message' => '关键字：'.$keywords.' 搜索出现错误 '.$response['code'].':'.$response['error']);
		else
		{
			$query = $this->request->makeUriByRequest('page={page}');
			$nav = new Typecho_Widget_Helper_PageNavigator_Box($response['results'], $page, $pageSize, $query);
			return array('result' => true, 'list' => $response['list'], 'nav' => $nav);
		}
	}
}

?>