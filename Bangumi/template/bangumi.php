<?php
/**
 * Bangumi
 *
 * @package custom
 */
 ?>

<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>

<?php $this->need('header.php'); ?>

<div id="part-main" role="main">
    <div class="post-content">
		<?php Bangumi_Plugin::render(); ?>
    </div>
</div><!-- end #main-->

<?php $this->need('sidebar.php'); ?>
<?php $this->need('footer.php'); ?>
