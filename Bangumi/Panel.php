<?php
include 'common.php';
include 'header.php';
include 'menu.php';
?>

<div class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        <div class="colgroup typecho-page-main" role="main">
            <div class="col-mb-12">
            	<?php if($options->plugin('Bangumi')->mode == 'cache'): ?>
					<div class="button_box">
						<a href="<?php $options->index('/action/bangumi?do=reload'); ?>" title="重载Bangumi缓存数据">刷新缓存</a>
					</div>
				<?php endif; ?>
				<?php Bangumi_Plugin::render(); ?>
			</div>
		</div>
	</div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
include 'footer.php';
?>