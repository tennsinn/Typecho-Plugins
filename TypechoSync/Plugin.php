<?php
/**
 * 同步发表
 * 
 * @package TypechoSync
 * @author 息E-敛
 * @version 0.3.0
 * @link http://tennsinn.com
 **/
 
 class TypechoSync_Plugin implements Typecho_Plugin_Interface
 {
	/* 激活插件方法 */
	public static function activate()
	{
		Typecho_Plugin::factory('Words_Action')->finishWord = array('TypechoSync_Plugin', 'syncWords');
		Typecho_Plugin::factory('Widget_Contents_Post_Edit')->write = array('TypechoSync_Plugin', 'syncPost');
		Typecho_Plugin::factory('admin/write-post.php')->option = array('TypechoSync_Plugin', 'optionWrite');
		return _t('插件已成功开启，请务必在设置中填写相关内容');
	}
 
	/* 禁用插件方法 */
	public static function deactivate()
	{
	}
 
	/* 插件配置方法 */
	public static function config(Typecho_Widget_Helper_Form $form)
	{
		$sync = new Typecho_Widget_Helper_Form_Element_Checkbox(
			'sync', 
			array(
				'post' => '同步文章',
				'words' => '同步心情碎语'
			), 
			NULL, 
			'同步设置', 
			_t('请选择需要同步的项目')
		);
		$form->addInput($sync->multiMode());

		$sinaKey = new Typecho_Widget_Helper_Form_Element_Text('sinaKey', NULL, '1644158214', _t('新浪微博App Key'), _t('请填入新浪微博应用的App Key，默认为TypechoSync的App Key'));
		$form->addInput($sinaKey);

		$sinaSecret = new Typecho_Widget_Helper_Form_Element_Text('sinaSecret', NULL, '6c768217fd54b6791e5d0ad0304c3906', _t('新浪微博App Secret'), _t('请填入新浪微博应用的App Secret，默认为TypechoSync的App Secret'));
		$form->addInput($sinaSecret);

		$sinaToken = new Typecho_Widget_Helper_Form_Element_Text('sinaToken', NULL, NULL, _t('新浪微博access_token'), _t('请填入获取到的access_token值，使用TypechoSync应用请<a href="http://blog.tennsinn.com/TypechoSync">点此进行授权（暂未可用）</a>'));
		$form->addInput($sinaToken);

		$template = new Typecho_Widget_Helper_Form_Element_Text('template', NULL, '{site}：发表了一篇博文《{title}》：{text}{permalink}', _t('发表模板'), _t('同步时使用的模板<br>{site}：站点名称；{title}：文章标题；{text}：文章摘要；{permalink}：网址链接<br>摘要默认长度100，请注意总长勿超过微博允许的字数'));
		$form->addInput($template);
	}
 
	/* 个人用户的配置方法 */
	public static function personalConfig(Typecho_Widget_Helper_Form $form)
	{
	}

	/**
	 * 文章写作页面同步开关
	 * 
	 * @param class $post 类Widget_Contents_Post_Edit
	 * @return void
	 */
	public static function optionWrite($postClass)
	{
		$settings = Helper::options()->plugin('TypechoSync');
		if(!empty($settings->sinaToken) && isset($settings->sync) && in_array('post', $settings->sync) && (!$postClass->have() || $postClass->type == 'post_draft' && $postClass->parent == 0))
		{
			$syncs = array('sina' => '同步至新浪微博');
		?>
			<section class="typecho-post-option category-option">
				<label class="typecho-label"><?php _e('同步选项'); ?></label>
				<ul>
				<?php foreach($syncs as $syncSlug => $syncText): ?>
					<li><input id="sync-<?=$syncSlug?>" type="checkbox" value="<?=$syncSlug?>" name="syncs[]"<?php if(isset($settings->sync) && in_array('post', $settings->sync)): ?> checked="true"<?php endif; ?>/><label for="sync-<?=$syncSlug?>"><?=$syncText?></label></li>
				<?php endforeach; ?>
				</ul>
			</section>
		<?php
		}
	}

	/**
	 * 同步发表文章
	 * 
	 * @param  array $content 文章内容
	 * @param  class $class 类Widget_Contents_Post_Edit
	 * @return array
	 */
	public static function syncPost($content, $class)
	{
		$settings = Helper::options()->plugin('TypechoSync');
		if(!empty($settings->sinaToken) && isset($settings->sync) && in_array('post', $settings->sync) 
			&& $class->request->is('do=publish') && (!$class->have() || $class->type == 'post_draft' && $class->parent == 0)
			&& Typecho_Widget::widget("Widget_User")->pass('editor', true) 
			&& (empty($contents['visibility']) || $contents['visibility'] == 'publish' || $contents['visibility'] == 'password')
		)
		{
			$syncs = array_unique(array_map('trim', $class->request->syncs));
			$options = Helper::options();
			if(in_array('sina', $syncs))
			{
				require_once('saetv2.ex.class.php');
				$sinaClient = new SaeTClientV2($settings->sinaKey, $settings->sinaSecret, $settings->sinaToken);
				// 处理文字
				$text = $content['text'];
				$text = Helper::options()->markdown ? MarkdownExtraExtended::defaultTransform($text) : Typecho_Common::cutParagraph($text);
				$text = Typecho_Common::fixHtml($text);
				// 获取第一张图片
				if(preg_match("/\<img.*?src\=\"(.*?)\"[^>]*>/i", $text, $pic))
					$pic = $pic[1];
				$text = explode('<!--more-->', $text);
				$text = Typecho_Common::subStr(strip_tags($text[0]), 0, 100, '...');
				// 获取短网址
				$apiUrl = 'https://api.weibo.com/2/short_url/shorten.json?access_token='.$settings->sinaToken.'&url_long='.urlencode($content['permalink']);
				$shotenUrl = file_get_contents($apiUrl);
				$shotenUrl = json_decode($shotenUrl);
				if($shotenUrl->urls[0]->url_short)
					$permalink = $shotenUrl->urls[0]->url_short;
				else
					$permalink = $options->siteUrl;
				// 模板文本
				$string = $settings->template ? $settings->template : '{site}：发表了一篇博文《{title}》：{text}{permalink}';
				$search = array('{site}', '{title}', '{text}', '{permalink}');
				$replace = array($options->title, $content['title'], $text, $permalink);
				$string = str_replace($search, $replace, $string);
				if($pic)
					$sinaClient->upload($string, $pic);
				else
					$sinaClient->update($string);
			}
		}
		return $content;
	}
 
	/**
	 * 同步心情碎语
	 * 
	 * @param  string $content 碎语内容
	 * @return void
	 */
	public static function syncWords($newWord)
	{
		$settings = Helper::options()->plugin('TypechoSync');
		if(!empty($settings->sinaToken) && isset($settings->sync) && in_array('words', $settings->sync))
		{
			require_once('saetv2.ex.class.php');
			$sinaClient = new SaeTClientV2($settings->sinaKey, $settings->sinaSecret, $settings->sinaToken);
			$sinaClient->update($newWord['content']);
		}
	}
}
?>