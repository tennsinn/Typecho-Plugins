<link rel="stylesheet" type="text/css" href="<?php Helper::options()->pluginUrl('Bangumi/template/bangumi.css'); ?>">
<div id="part-list" role="main">
	<?php $response = Typecho_Widget::widget('Bangumi_Action')->getCollection(); ?>
	<?php if($response['status']): ?>
	<ul class="row_double">
		<?php $trans = array('阅读', '观看', '收听', '游戏', '', '欣赏'); ?>
		<?php foreach($response['list'] as $bangumi): ?>
		<li class="bangumi-content" id="subject-<?php echo $bangumi['subject_id']; ?>">
			<div class="bangumi-img"><img src="<?php echo $bangumi['image']; ?>"></div>
			<div class="bangumi-info">
				<div><i class="subject-type-ico subject-type-<?php echo $bangumi['type']; ?>"></i><a href="http://bangumi.tv/subject/<?php echo $bangumi['subject_id']; ?>"><?php echo $bangumi['name']; ?></a></div><div><small><?php echo $bangumi['name_cn']; ?></small></div>
				<div class="bangumi-meta">最后<?php echo $trans[$bangumi['type']-1]; ?>：<?php echo date("Y-m-d", $bangumi['lasttouch']+Helper::options()->timezone); ?></div>
				<?php if($bangumi['type'] == 2 || $bangumi['type'] == 6): ?>
					<div class="bangumi-progress">
						<div class="bangumi-progress-inner" style="color:white; width:<?php echo $bangumi['eps'] ? $bangumi['ep_status']/$bangumi['eps']*100 : 100; ?>%"><small><?php echo $bangumi['ep_status']; ?> / <?php echo $bangumi['eps'] ? $bangumi['eps'] : '??'; ?></small></div>
					</div>
				<?php endif; ?>
			</div>
		</li>
		<?php endforeach; ?>
	</ul>
	<div class="clear"></div>
	<?php else: ?>
	<div><?php echo $response['message']; ?></div>
	<?php endif; ?>
</div>