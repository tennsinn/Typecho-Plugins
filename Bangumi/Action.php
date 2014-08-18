<?php

class Bangumi_Action extends Typecho_Widget implements Widget_Interface_Do
{
	public function __construct($request, $response, $params = NULL)
	{
		parent::__construct($request, $response, $params);
		$this->_settings = Helper::options()->plugin('Bangumi');
		$this->_db = Typecho_Db::get();
	}

	public function action()
	{
		$this->widget("Widget_User")->pass("administrator");
		$this->on($this->request->is('do=editSubject'))->editSubject();
		$this->on($this->request->is('do=editCollection'))->editCollection();
		$this->on($this->request->is('do=syncBangumi'))->syncBangumi();
	}

	/**
	 * 获取Bangumi对应账号收视信息（未完成）
	 * @return array
	 */
	private function syncBangumi()
	{
		if(!$this->_settings->id)
			return array('status' => false, 'message' => '未设置Bangumi用户id');
		$response = @file_get_contents('http://api.bgm.tv/user/'.$this->_settings->id.'/collection?cat=watching');
		$response = json_decode($response, true);
		if(!$response)
			return array('status' => false, 'message' => '获取数据出错');
		return array('status' => true);
	}

	/**
	 * 收藏修改（未完成）
	 * @return void
	 */
	private function editCollection()
	{
		$collection = isset($this->request->collection) ? $this->request->get('collection') : 'do';
		$ids = $this->request->filter('int')->id;
		foreach ($ids as $id)
		{
		}
	}

	/**
	 * 记录信息编辑（未完成）
	 * @return void
	 */
	private function editSubject()
	{
	}

	/**
	 * 获取收藏条目
	 * @param  integer $pageSize 分页大小
	 * @return array
	 */
	public function getCollection($pageSize=10)
	{
		$collection = isset($this->request->type) ? $this->request->get('type') : 'do';
		$num = $this->_db->fetchObject($this->_db->select(array('COUNT(table.bangumi.id)' => 'num'))->from('table.bangumi')->where('collection = ?', $collection))->num;
		if(!$num)
			return array('status' => false, 'message' => '存在0条记录');
		$page = isset($this->request->page) ? $this->request->get('page') : 1;
		$rows = $this->_db->fetchAll($this->_db->select()->from('table.bangumi')->where('collection = ?', $collection)->order('lasttouch')->page($page, $pageSize));
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
		$page = isset($this->request->page) ? $this->request->get('page') : 1;
		$keywords = $this->request->get('keywords');
		$response = @file_get_contents('http://api.bgm.tv/search/subject/'.$keywords.'?responseGroup=large&max_results='.$pageSize.'&start='.($page-1)*$pageSize);
		$response = json_decode($response, true);
		if(!$response || isset($response['error']))
			return array('status' => false, 'message' => '搜索出现错误');
		elseif(!$response['list'])
			return array('status' => false, 'message' => '搜索到0个结果');
		else
		{
			$query = $this->request->makeUriByRequest('page={page}');
			$nav = new Typecho_Widget_Helper_PageNavigator_Box($response['results'], $page, $pageSize, $query);
			return array('status' => true, 'list' => $response['list'], 'nav' => $nav);
		}
	}
}

?>