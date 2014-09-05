<?php
	$collection_trans = array('do' => array('在读', '在看', '在听', '在玩', '', '在看'), 'collect' => array('读过', '看过', '听过', '玩过', '', '看过'), 'wish' => array('想读', '想看', '想听', '想玩', '', '想看'), 'on_hold' => array('搁置', '搁置', '搁置', '搁置', '', '搁置'), 'dropped' => array('抛弃', '抛弃', '抛弃', '抛弃', '', '抛弃'));
	$type_trans = array('阅读', '观看', '收听', '游戏', '', '欣赏');
?>
<link rel="stylesheet" type="text/css" href="<?php Helper::options()->pluginUrl('Bangumi/template/bangumi.css'); ?>">
<div id="part-list" role="main">
	<?php $response = Typecho_Widget::widget('Bangumi_Action')->getCollection(); ?>
	<?php if($response['status']): ?>
	<ol class="bangumi-navigator">
		<?php $response['nav']->render(_t('&laquo;'), _t('&raquo;')); ?>
	</ol>
	<ul class="row_double">
		<?php foreach($response['list'] as $bangumi): ?>
		<li class="bangumi-content" id="subject-<?php echo $bangumi['subject_id']; ?>">
			<div class="bangumi-img"><img src="<?php echo $bangumi['image']; ?>"></div>
			<div class="bangumi-info">
				<div><i class="subject_type-ico subject_type-<?php echo $bangumi['type']; ?>"></i><a href="http://bangumi.tv/subject/<?php echo $bangumi['subject_id']; ?>"><?php echo $bangumi['name']; ?></a></div><div><small><?php echo $bangumi['name_cn']; ?></small></div>
				<div class="bangumi-meta">
					<div>状态：<?php echo $collection_trans[$bangumi['collection']][$bangumi['type']-1]; ?>
					<div>最后<?php echo $type_trans[$bangumi['type']-1]; ?>：<?php echo date("Y-m-d", $bangumi['lasttouch']+Helper::options()->timezone); ?></div>
				</div>
				<?php if($bangumi['type'] == 2 || $bangumi['type'] == 6): ?>
					<div class="bangumi-progress">
						<div class="bangumi-progress-inner" style="color:white; width:<?php echo $bangumi['eps'] ? $bangumi['ep_status']/$bangumi['eps']*100 : 100; ?>%"><small><?php echo $bangumi['ep_status']; ?> / <?php echo $bangumi['eps'] ? $bangumi['eps'] : '??'; ?></small></div>
					</div>
				<?php endif; ?>
			</div>
			<div class="bangumi-review">
				<div><i>评价：</i><?php echo str_repeat('<span class="interest_rate-star interest_rate-star-rating"></span>', $bangumi['interest_rate']); echo str_repeat('<span class="interest_rate-star interest_rate-star-blank"></span>', 10-$bangumi['interest_rate']); ?></div>
				<div><i>标签：</i><?php echo $bangumi['tags'] ? $bangumi['tags'] : '无'; ?></div>
				<div><i>吐槽：</i><?php echo $bangumi['comment'] ? $bangumi['comment'] : '无'; ?></div>
			</div>
		</li>
		<?php endforeach; ?>
	</ul>
	<div class="clear"></div>
	<?php else: ?>
	<div><?php echo $response['message']; ?></div>
	<?php endif; ?>
</div>