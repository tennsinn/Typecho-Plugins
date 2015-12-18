<?php
$status_trans = array(
	'all' => array('全部', '书籍', '动画', '音乐', '游戏', '广播', '影视'),
	'do' => array('进行', '在读', '在看', '在听', '在玩', '在听', '在看'),
	'collect' => array('完成', '读过', '看过', '听过', '玩过', '听过', '看过'),
	'wish' => array('计划', '想读', '想看', '想听', '想玩', '想听', '想看'),
	'on_hold' => array('搁置', '搁置', '搁置', '搁置', '搁置', '搁置', '搁置'),
	'dropped' => array('抛弃', '抛弃', '抛弃', '抛弃', '抛弃', '抛弃', '抛弃')
);
$progress_trans = array(
	'Collection' => array('收藏', '本篇', '特典'),
	'Series' => array('系列', '卷', '番外'),
	'Tankōbon' => array('单行本', '章', '节'),
	'TV' => array('TV', '本篇', '特典'),
	'OVA' => array('OVA', '本篇', '特典'),
	'EP' => array('EP', '本篇', '特典'),
	'Album' => array('Album', '本篇', '特典'),
	'Android' => array('Andriod', '本篇', '特典'),
	'PSV' => array('PSV', '奖杯', '收集'),
	'3DS' => array('3DS', '本篇', '特典'),
	'PC' => array('PC', '路线', '收集'),
	'RadioDrama' => array('广播剧', '本篇', '番外'),
	'Teleplay' => array('电视剧', '本篇', '特典'),
	'TalkShow' => array('脱口秀', '本篇', '特典'),
	'Movie' => array('电影', '本篇', '特典')
);
$timeoffset = Helper::options()->timezone;
?>
<link rel="stylesheet" type="text/css" href="<?php Helper::options()->pluginUrl('Collection/template/stylesheet-common.css'); ?>">
<link rel="stylesheet" type="text/css" href="<?php Helper::options()->pluginUrl('Collection/template/stylesheet-page.css'); ?>">
<div id="part-list" role="main">
	<?php $response = Typecho_Widget::widget('Collection_Action')->getCollection(); ?>
	<?php if($response['result']): ?>
	<ol class="collection-navigator">
		<?php $response['nav']->render(_t('&laquo;'), _t('&raquo;')); ?>
	</ol>
	<ul class="collection-list">
		<?php foreach($response['list'] as $subject): ?>
		<li class="subject-content" id="subject-<?php echo $subject['id']; ?>">
			<?php
				echo '<div class="subject-cover"><img src="';
				if(!$subject['image'])
					Helper::options()->pluginUrl('Collection/template/default_cover.jpg');
				elseif($subject['bangumi_id'])
					echo 'http://lain.bgm.tv/pic/cover/m/'.$subject['image'];
				else
					echo Helper::options()->plugin('Collection')->imageUrl ? $options->plugin('Collection')->imageUrl.'m/'.$subject['image'] : $subject['image'];;
				echo '"></div>';
			?>
			<div class="subject-info">
				<div class="subject-name">
					<i class="subject_class-ico subject_class-<?php echo $subject['class']; ?>"></i>
					<?php echo $subject['bangumi_id'] ? '<a href="http://bangumi.tv/subject/'.$subject['bangumi_id'].'" rel="nofollow">'.$subject['name'].'</a>' : $subject['name']; ?>
					<?php echo $subject['name_cn'] ? '<small>（'.$subject['name_cn'].'）</small>' : ''; ?>
				</div>
				<div class="subject-meta">
					<span>记录起止：<?php echo $subject['time_start'] ? date("Y-m-d", $subject['time_start']+$timeoffset) : '??'; ?> / <?php echo $subject['time_finish'] ? date("Y-m-d", $subject['time_finish']+$timeoffset) : '??'; ?></span>
					<span>最后修改：<?php echo date("Y-m-d", $subject['time_touch']+$timeoffset); ?></span>
				</div>
				<div class="subject-box-progress">
					<div>状态：</div>
					<div><?php _e($status_trans[$subject['status']][$subject['class']]); ?></div>
				<?php if(!is_null($subject['ep_count']) && !is_null($subject['ep_status'])): ?>
						<div><?php _e($progress_trans[$subject['type']][1].'：'); ?></div>
						<div class="subject-progress"><div class="subject-progress-inner" style="color:white; width:<?php echo ($subject['ep_count'] ? $subject['ep_status']/$subject['ep_count']*100 : 50); ?>%"><small><?php echo $subject['ep_status'].' / '.($subject['ep_count'] ? $subject['ep_count'] : '??'); ?></small></div></div>
					<?php if(!is_null($subject['sp_count']) && !is_null($subject['sp_status'])): ?>
						<div><?php _e($progress_trans[$subject['type']][2].'：'); ?></div>
						<div class="subject-progress"><div class="subject-progress-inner" style="color:white; width:<?php echo ($subject['sp_count'] ? $subject['sp_status']/$subject['sp_count']*100 : 50); ?>%"><small><?php echo $subject['sp_status'].' / '.($subject['sp_count'] ? $subject['sp_count'] : '??'); ?></small></div></div>
					<?php endif; ?>
				<?php endif; ?>
				</div>
				<div class="subject-review">
					<div><i>评价：</i><?php echo str_repeat('<span class="rate-star rate-star-rating"></span>', $subject['rate']); echo str_repeat('<span class="rate-star rate-star-blank"></span>', 10-$subject['rate']); ?></div>
					<div><i>标签：</i><span><?php echo $subject['tags'] ? $subject['tags'] : '无'; ?></span></div>
					<div><i>吐槽：</i><span><?php echo $subject['comment'] ? $subject['comment'] : '无'; ?></span></div>
				</div>
			</div>

		</li>
		<?php endforeach; ?>
	</ul>
	<div class="clear"></div>
	<?php else: ?>
	<div><?php echo $response['message']; ?></div>
	<?php endif; ?>
</div>