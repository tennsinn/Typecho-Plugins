<?php
/**
 * 一个模仿Bangumi同时利用其部分信息的本地收藏整理展示插件
 * 
 * @package Collection
 * @author 息E-敛
 * @version 1.1.3
 * @link http://tennsinn.com
 */
class Collection_Plugin implements Typecho_Plugin_Interface
{
	public static function activate()
	{
		Helper::addAction('collection', 'Collection_Action');
		Helper::addPanel(3, "Collection/Panel.php", _t("Collection"), _t("Collection"), 'administrator', false, 'extending.php?panel=Collection%2FPanel.php&do=new');
		$db = Typecho_Db::get();
		$charset = Helper::options()->charset == 'UTF-8' ? 'utf8' : 'gbk';
		$query = 'CREATE TABLE IF NOT EXISTS '. $db->getPrefix() . 'collection' ." (
			`id` int(10) unsigned NOT NULL auto_increment PRIMARY KEY,
			`type` int(1) unsigned NOT NULL,
			`name` varchar(50) NOT NULL,
			`name_cn` varchar(50) default NULL,
			`image` varchar(100) default NULL,
			`ep_count` int(3) unsigned default '0',
			`sp_count` int(2) unsigned default '0',
			`notes` varchar(50) default NULL,
			`bangumi_id` int(10) unsigned default NULL,
			`status` char(7) NOT NULL,
			`time_start` int(10) unsigned default NULL,
			`time_finish` int(10) unsigned default NULL,
			`time_touch` int(10) unsigned default NULL,
			`ep_status` int(3) unsigned default '0',
			`sp_status` int(2) unsigned default '0',
			`rate` int(2) unsigned default NULL,
			`tags` varchar(100) default NULL,
			`comment` text
			) ENGINE=MyISAM DEFAULT CHARSET=". $charset;
		$db->query($query);
	}
	
	public static function deactivate()
	{
		Helper::removeAction('collection');
		Helper::removePanel(3, 'Collection/Panel.php');
		if (Helper::options()->plugin('Collection')->drop)
		{
			$db = Typecho_Db::get();
			$db->query('DROP TABLE IF EXISTS '.$db->getPrefix().'collection');
			return('插件已经禁用, 插件数据已经删除');
		}
		else
			return('插件已经禁用, 插件数据保留');
	}
	
	public static function config(Typecho_Widget_Helper_Form $form)
	{
		$drop = new Typecho_Widget_Helper_Form_Element_Radio('drop', array(0 => _t('不刪除'), 1 => _t('刪除')), 0, _t('禁用时是否删除数据'), _t('选择在禁用插件的同时是否删除数据库中的插件数据内容'));
		$form->addInput($drop);

		$uid = new Typecho_Widget_Helper_Form_Element_Text(
			'uid', 
			NULL, 
			NULL, 
			_t('Bangumi用户uid'), 
			_t('Bangumi的用户uid，可用于获取部分收藏信息')
		);
		$form->addInput($uid);

		$imageUrl = new Typecho_Widget_Helper_Form_Element_Text(
			'imageUrl',
			NULL,
			NULL,
			_t('默认图片路径'),
			_t('对于自行添加的记录默认Cover路径，为空时将直接输出记录图片地址')
		);
		$form->addInput($imageUrl);
	}
	
	public static function personalConfig(Typecho_Widget_Helper_Form $form){}

	public static function render()
	{
		$export = Typecho_Plugin::export();
		if(array_key_exists('Collection', $export['activated']))
			include 'template/template.php';
		else
			echo '<div>Collection 插件未开启</div>';
	}
}
?>