<?php

class Bangumi_Action extends Typecho_Widget implements Widget_Interface_Do
{
	private $_settings;

	public function __construct($request, $response, $params = NULL)
	{
		parent::__construct($request, $response, $params);
		$this->_settings = Helper::options()->plugin('Bangumi');
	}

	public function action()
	{
		$this->on($this->request->is('do=reload'))->reload();
	}

	private function reload()
	{
		if($this->_settings->id == NULL)
			$this->widget('Widget_Notice')->set(_t('未设置Bangumi用户id'), 'notice');
		else
		{
			$bangumis = @file_get_contents('http://api.bgm.tv/user/'.$this->_settings->id.'/collection?cat=watching');
			$bangumis = $this->restoreData($bangumis);
			@file_put_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'bangumis.json', $bangumis);
			$this->widget('Widget_Notice')->set(_t('已成功更新Bangumi缓存数据'), 'success');
		}
		$this->response->goBack();
	}

	private function restoreData($bangumis)
	{
		$bangumis = json_decode($bangumis, true);
		$items = $this->_settings->item;
		$news = array();
		foreach($bangumis as $bangumi)
		{
			$new = array('name' => $bangumi['name'], 'ep_status' => $bangumi['ep_status'], 'lasttouch' => $bangumi['lasttouch']);
			foreach($items as $item)
				$new[$item] = $bangumi['subject'][$item];
			$new['image'] = $bangumi['subject']['images'][$this->_settings->image];
			$news[$bangumi['subject']['id']] = $new;
		}
		$news = json_encode($news);
		return $news;
	}

	public function getBangumi()
	{
		if($this->_settings->id == NULL)
			return false;
		else
		{
			if($this->_settings->mode == 'get')
			{
				$bangumis = @file_get_contents('http://api.bgm.tv/user/'.$this->_settings->id.'/collection?cat=watching');
				$bangumis = $this->restoreData($bangumis);
			}
			elseif(!file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.'bangumis.json'))
			{
				$bangumis = @file_get_contents('http://api.bgm.tv/user/'.$this->_settings->id.'/collection?cat=watching');
				$bangumis = $this->restoreData($bangumis);
				@file_put_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'bangumis.json', $bangumis);
			}
			else
			{
				$bangumis = @file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'bangumis.json');
			}
			$bangumis = json_decode($bangumis, true);
			return $bangumis;
		}
	}
}

?>