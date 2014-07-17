<link rel="stylesheet" type="text/css" href="<?php Helper::options()->pluginUrl('Bangumi/template/bangumi.css'); ?>">
<div id="part-list" role="main">
	<?php $bangumis = Typecho_Widget::widget('Bangumi_Action')->getBangumi(); ?>
	<?php if($bangumis != false): ?>
	<ul class="row_double">
		<?php foreach($bangumis as $id => $bangumi): ?>
		<li class="bangumi-content" id="<?php echo $id; ?>">
			<div class="bangumi-img"><img src="<?php echo $bangumi['image']; ?>"></div>
			<div class="bangumi-info">
				<p class="bangumi-title"><a rel="nofollow" href="<?php echo $bangumi['url']; ?>"><?php echo $bangumi['name']; ?></a></p>
				<p class="bangumi-title-cn"><small>(<?php echo $bangumi['name_cn']; ?>)</small></p>
				<p class="bangumi-meta">最后观看：<?php echo date("Y-m-d", $bangumi['lasttouch']); ?></p>
				<div class="bangumi-progress">
					<?php
						if($bangumi['eps'] == 0){
							$width = 100;
						}
						else{
							$width = $bangumi['ep_status']/$bangumi['eps']*100;
						}
					?>
					<div class="bangumi-progress-inner" style="color: <?php echo ($width>10?'#FFF':'#000'); ?>; width:<?php echo $width; ?>%">&nbsp;<small><?php echo $bangumi['ep_status']; ?>/<?php echo $bangumi['eps']; ?></small></div>
				</div>
			</div>
		</li>
		<?php endforeach; ?>
	</ul>
	<div class="clear"></div>
	<?php else: ?>
	<div>获取失败或暂无在看动漫。</div>
	<?php endif; ?>
</div>