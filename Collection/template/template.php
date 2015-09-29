<?php
	$status_trans = array(
		'do' => array('在读', '在看', '在听', '在玩', '在看', '在看', '在看', '在听'), 
		'collect' => array('读过', '看过', '听过', '玩过', '看过', '看过', '看过', '听过'), 
		'wish' => array('想读', '想看', '想听', '想玩', '想看', '想看', '想看', '想听'), 
		'on_hold' => array('搁置', '搁置', '搁置', '搁置', '搁置', '搁置', '搁置', '搁置'), 
		'dropped' => array('抛弃', '抛弃', '抛弃', '抛弃', '抛弃', '抛弃', '抛弃', '抛弃')
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
					<i class="subject_type-ico subject_type-<?php echo $subject['type']; ?>"></i>
					<?php echo $subject['bangumi_id'] ? '<a href="http://bangumi.tv/subject/'.$subject['bangumi_id'].'">'.$subject['name'].'</a>' : $subject['name']; ?>
					<?php echo $subject['name_cn'] ? '<small>（'.$subject['name_cn'].'）</small>' : ''; ?>
				</div>
				<div class="subject-meta">
					<span>状态：<?php echo $status_trans[$subject['status']][$subject['type']-1]; ?></span>
					<span>记录起止：<?php echo $subject['time_start'] ? date("Y-m-d", $subject['time_start']+$timeoffset) : '??'; ?> / <?php echo $subject['time_finish'] ? date("Y-m-d", $subject['time_finish']+$timeoffset) : '??'; ?></span>
					<span>最后修改：<?php echo date("Y-m-d", $subject['time_touch']+$timeoffset); ?></span>
				</div>
				<div class="subject-box-progress">
				<?php if($subject['type'] == 1 || $subject['type'] == 2 || $subject['type'] == 6): ?>
						<div><?php _e('本篇：'); ?></div>
						<div class="subject-progress"><div class="subject-progress-inner" style="color:white; width:<?php echo ($subject['ep_count'] ? $subject['ep_status']/$subject['ep_count']*100 : 50); ?>%"><small><?php echo $subject['ep_status'].' / '.($subject['ep_count'] ? $subject['ep_count'] : '??'); ?></small></div></div>
					<?php if($subject['sp_count'] || $subject['sp_status']): ?>
						<div><?php _e('特典：'); ?></div>
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