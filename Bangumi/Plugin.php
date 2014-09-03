<?php
/**
 * 一个利用Bangumi信息的ACG本地整理插件
 * 
 * @package Bangumi
 * @author 息E-敛
 * @version 0.9.1
 * @link http://tennsinn.com
 */
class Bangumi_Plugin implements Typecho_Plugin_Interface
{
	public static function activate()
	{
		Helper::addAction('bangumi', 'Bangumi_Action');
		Helper::addPanel(3, "Bangumi/Panel.php", _t("Bangumi"), _t("Bangumi"), 'administrator', false, 'extending.php?panel=Bangumi%2FPanel.php&do=new');
		$db = Typecho_Db::get();
		$charset = Helper::options()->charset == 'UTF-8' ? 'utf8' : 'gbk';
		$query = 'CREATE TABLE IF NOT EXISTS '. $db->getPrefix() . 'bangumi' ." (
			`id` int(10) unsigned NOT NULL auto_increment PRIMARY KEY,
			`subject_id` int(10) unsigned NOT NULL,
			`type` int(1) unsigned NOT NULL,
			`name` varchar(200) NOT NULL,
			`name_cn` varchar(200) default NULL,
			`image` varchar(200) default NULL,
			`lasttouch` int(10) unsigned default '0',
			`collection` varchar(50) default NULL,
			`interest_rate` int(2) unsigned default '0',
			`tags` varchar(200) default NULL,
			`comment` text default NULL,
			`eps` int(5) unsigned default '0',
			`ep_status` int(5) unsigned default '0'
			) ENGINE=MyISAM DEFAULT CHARSET=". $charset;
		$db->query( $query );
	}
	
	public static function deactivate()
	{
		Helper::removeAction('bangumi');
		Helper::removePanel(3, 'Bangumi/Panel.php');
		if (Helper::options()->plugin('Bangumi')->drop)
		{
			$db = Typecho_Db::get();
			$db->query('DROP TABLE IF EXISTS '.$db->getPrefix().'bangumi');
			return('插件已经禁用, 插件数据已经删除');
		}
		else
			return('插件已经禁用, 插件数据保留');
	}
	
	public static function config(Typecho_Widget_Helper_Form $form)
	{
		$drop = new Typecho_Widget_Helper_Form_Element_Radio('drop', array(0 => _t('不刪除'), 1 => _t('刪除')), 0, _t('禁用时是否删除数据'), _t('选择在禁用插件的同时是否删除数据库中的插件数据内容'));
		$form->addInput($drop);

		$id = new Typecho_Widget_Helper_Form_Element_Text(
			'id', 
			NULL, 
			NULL, 
			_t('Bangumi用户id'), 
			_t('Bangumi的用户id，可用于获取部分收藏信息')
		);
		$form->addInput($id);

		$image = new Typecho_Widget_Helper_Form_Element_Radio(
			'image', 
			array('large' => 'large', 'common' => 'common', 'medium' => 'medium', 'small' => 'small', 'grid' => 'grid'), 
			'medium', 
			_t('图像大小'), 
			_t('选择保存的图像大小url类别')
		);
		$form->addInput($image);
	}
	
	public static function personalConfig(Typecho_Widget_Helper_Form $form){}

	public static function render()
	{
		$export = Typecho_Plugin::export();
		if(array_key_exists('Bangumi', $export['activated']))
			include 'template/template.php';
		else
			echo '<div>Bangumi 插件未开启</div>';
	}
}
?>