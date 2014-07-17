<?php
/**
 * 心情碎语
 * 
 * @package Words
 * @author 息E-敛
 * @version 1.1.1
 * @link http://tennsinn.com
 **/
 
 class Words_Plugin implements Typecho_Plugin_Interface
 {
	/* 激活插件方法 */
	public static function activate()
	{
		Helper::addAction('words', 'Words_Action');
		Helper::addPanel(3, 'Words/Panel.php', _t('Words'), _t('Words'), 'administrator', false, 'extending.php?panel=Words%2FPanel.php&type=new');
		$db = Typecho_Db::get();
		$charset = Helper::options()->charset == 'UTF-8' ? 'utf8' : 'gbk';
		$query = 'CREATE TABLE IF NOT EXISTS '. $db->getPrefix() . 'words_contents' ." (
			`wid` int(10) unsigned NOT NULL auto_increment PRIMARY KEY,
			`created` int(10) unsigned default '0',
			`expression` varchar(200) default NULL,
			`content` text NOT NULL,
			`commentsNum` int(10) unsigned  default '0'
			 ) ENGINE=MyISAM DEFAULT CHARSET=". $charset;
		$db->query( $query );
		$query = 'CREATE TABLE IF NOT EXISTS '. $db->getPrefix() . 'words_comments' ." (
			`cid` int(10) unsigned NOT NULL auto_increment PRIMARY KEY,
			`wid` int(10) unsigned NOT NULL,
			`created` int(10) unsigned default '0',
			`author` varchar(200) default NULL,
			`mail` varchar(200) default NULL,
			`url` varchar(200) default NULL,
			`ip` varchar(64) default NULL,
			`agent` varchar(200) default NULL,
			`content` text NOT NULL,
			`parent` int(10) unsigned default '0'
			 ) ENGINE=MyISAM DEFAULT CHARSET=". $charset;
		$db->query( $query );
		return _t('插件已成功开启，若需使用邮件提醒请进入设置修改相应信息');
	}
 
	/* 禁用插件方法 */
	public static function deactivate()
	{
		Helper::removeAction('words');
		Helper::removePanel(3, 'Words/Panel.php', _t('Words'), _t('Words'), 'administrator');
		$db = Typecho_Db::get();
		if (Helper::options()->plugin('Words')->drop)
		{
			$db->query('DROP TABLE IF EXISTS '.$db->getPrefix().'words_contents');
			$db->query('DROP TABLE IF EXISTS '.$db->getPrefix().'words_comments');
			return('插件已经禁用, 插件数据已经删除');
		}
		else
			return('插件已经禁用, 插件数据保留');
	}
 
	/* 插件配置方法 */
	public static function config(Typecho_Widget_Helper_Form $form)
	{
		$drop = new Typecho_Widget_Helper_Form_Element_Radio('drop', array(0 => _t('不刪除'), 1 => _t('刪除')), 0, _t('禁用时是否删除数据'), _t('选择在禁用插件的同时是否删除数据库中的插件数据内容'));
		$form->addInput($drop);

		$notice = new Typecho_Widget_Helper_Form_Element_Checkbox('notice',  array('admin' => '有新评论时，发邮件通知博主', 'reviewer' => '有评论被回复时，发邮件通知评论者', 'log' => '记录邮件发送日志'), NULL, '邮件发送设置', _t('日志记录在phpmailer/log.txt文件中'));
		$form->addInput($notice->multiMode());

		$page = new Typecho_Widget_Helper_Form_Element_Text('page', NULL, NULL, _t('碎语页面地址'), _t('请填写碎语页面地址，无需站点首页前缀，若填写则在邮件中添加对应链接'));
		$form->addInput($page);

		$host = new Typecho_Widget_Helper_Form_Element_Text('host', NULL, 'smtp.', _t('SMTP地址'), _t('请填写SMTP服务器地址'));
		$form->addInput($host->addRule('required', _t('必须填写一个正确的SMTP服务器地址')));

		$ssl = new Typecho_Widget_Helper_Form_Element_Radio('ssl', array(0 => _t('关闭ssl加密'), 1 => _t('启用ssl加密')), 0, _t('ssl加密'), _t('选择是否开启ssl加密选项'));
		$form->addInput($ssl);

		$port = new Typecho_Widget_Helper_Form_Element_Text('port', NULL, '25', _t('SMTP端口'), _t('SMTP服务端口，一般为25，ssl加密时一般为465'));
		$form->addInput($port->addRule('required', _t('必须填写相应的SMTP服务端口'))->addRule('isInteger', _t('端口号必须是纯数字')));

		$auth = new Typecho_Widget_Helper_Form_Element_Radio('auth', array(0 => _t('服务器无需验证'), 1 => _t('服务器需要验证')), 1, _t('服务器验证'), _t('选择服务器是否需要验证'));
		$form->addInput($auth);

		$username = new Typecho_Widget_Helper_Form_Element_Text('username', NULL, NULL, _t('SMTP用户名'),_t('SMTP服务验证用户名，一般为邮箱名如：youname@domain.com'));
		$form->addInput($username->addRule('required', _t('SMTP服务验证用户名')));

		$password = new Typecho_Widget_Helper_Form_Element_Password('password', NULL, NULL, _t('SMTP密码'));
		$form->addInput($password->addRule('required', _t('SMTP服务验证密码')));

		$address = new Typecho_Widget_Helper_Form_Element_Text('address', NULL, NULL, _t('博主接收邮箱'),_t('碎语发布者接收邮件用的信箱'));
		$form->addInput($address->addRule('required', _t('必须填写一个正确的接收邮箱'))->addRule('email', _t('请填写正确的邮箱！')));

		$titleAdmin = new Typecho_Widget_Helper_Form_Element_Text('titleAdmin', NULL, '{site}：心情碎语有了新的评论', _t('博主接收的邮件标题'));
		$form->addInput($titleAdmin);

		$titileReviewer = new Typecho_Widget_Helper_Form_Element_Text('titileReviewer', NULL, '{site}：您在心情碎语的评论有了回复', _t('评论者接收邮件标题'));
		$form->addInput($titileReviewer);
	}
 
	/* 个人用户的配置方法 */
	public static function personalConfig(Typecho_Widget_Helper_Form $form)
	{
	}
 
	/* 插件实现方法 */
	public static function render()
	{
		$export = Typecho_Plugin::export();
		if(array_key_exists('Words', $export['activated']))
			include 'template/template.php';
		else
			echo '<div>插件未开启</div>';
	}
}
?>