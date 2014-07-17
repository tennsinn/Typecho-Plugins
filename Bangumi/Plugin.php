<?php
/**
 * Bangumi在看信息获取展示插件
 * 
 * @package Bangumi
 * @author 息E-敛
 * @version 0.5.0
 * @link http://tennsinn.com
 */
class Bangumi_Plugin implements Typecho_Plugin_Interface
{
	public static function activate()
	{
		Helper::addAction('bangumi', 'Bangumi_Action');
		Helper::addPanel(3, "Bangumi/Panel.php", _t("Bangumi"), _t("Bangumi"), 'administrator');
		return _t('插件已开启，为保证插件正常工作，请进入设置进行调整，进入管理-Bangumi页面可进行管理');
	}
	
	public static function deactivate()
	{
		Helper::removeAction('bangumi');
		Helper::removePanel(3, 'Bangumi/Panel.php');
	}
	
	public static function config(Typecho_Widget_Helper_Form $form)
	{
		$id = new Typecho_Widget_Helper_Form_Element_Text(
			'id', 
			NULL, 
			NULL, 
			_t('Bangumi用户id'), 
			_t('Bangumi的用户id，用于获取收藏信息')
		);
		$form->addInput($id);

		$mode = new Typecho_Widget_Helper_Form_Element_Radio(
			'mode', 
			array('cache' => '缓存模式', 'get' => '获取模式'), 
			'get', 
			_t('内容获取模式'), 
			_t('缓存模式：会保存信息到插件目录下，数据需要手动在管理-Bangumi页面更新<br>获取模式：不会保存信息，每次打开页面时临时获取数据')
		);
		$form->addInput($mode);

		$item = new Typecho_Widget_Helper_Form_Element_Checkbox(
			'item',
			array(
				'url' => _t('链接到番剧的URL'),
				'type' => _t('番剧类型'),
				'name_cn' => _t('番剧汉化名'),
				'summary' => _t('番剧介绍'),
				'eps' => _t('集数'),
				'air_date' => _t('放映开始日期'),
				'air_weekday' => _t('放映日'),
				'collection' => _t('人数信息')
			),
			array(
				'id',
				'url',
				'name_cn',
				'eps'
			),
			_t('保留信息'),
			_t('选择所要保存的信息项目，默认保存番剧名称、追番集数、上次更新时间')
		);
		$form->addInput($item->multiMode());

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
			echo '<div>插件未开启</div>';
	}
}
?>