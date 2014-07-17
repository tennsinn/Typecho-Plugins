<?php
/**
 * 心情碎语
 *
 * @package custom
 */
 ?>
 
 <?php $this->need('header.php'); ?>

<div id="part-main" role="main">
    <div class="post-content">
		<?php Words_Plugin::render(); ?>
    </div>
</div><!-- end #main-->

<?php $this->need('sidebar.php'); ?>
<?php $this->need('footer.php'); ?>

