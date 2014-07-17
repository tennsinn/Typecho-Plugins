<link rel="stylesheet" type="text/css" href="<?php Helper::options()->pluginUrl('/Words/template/words.css'); ?>">
<script type="text/javascript" src="<?php Helper::options()->pluginUrl('/Words/template/words.js'); ?>"></script>
<?php $wordsPage = Typecho_Widget::widget('Words_Action')->getWords(); ?>
<ol class="words-navigator">
	<?php $wordsPage['nav']->render(_t('&laquo;'), _t('&raquo;')); ?>
</ol>
<div class="words-page">
	<?php foreach ($wordsPage['words'] as $word): ?>
		<div class="words-list" id="words-<?php echo $word['wid']; ?>">
			<div class="words-expression"><img src="<?php Helper::options()->pluginUrl('/Words/expression/'.$word['expression']); ?>"></div>
			<div class="words-contents">
				<div class="words-words">
					<p><?php echo $word['content']; ?></p>
					<p wid="<?php echo $word['wid']; ?>" class="words-button">吐槽(<?php echo $word['commentsNum']; ?>)</p>
					<time class="words-meta" datetime="<?php echo date('c', $word['created']+Helper::options()->timezone); ?>"><?php echo date('Y-m-d H:i', $word['created']+Helper::options()->timezone); ?></time>
				</div>
				<?php $comments = Typecho_Widget::widget('Words_Action')->getcomments('related', $word['wid']); ?>
				<?php foreach($comments as $comment): ?>
					<div class="words-comments" id="words-comments-<?php echo $comment['cid'] ?>">
						<img class="words-avatar" src="<?php echo 'http://www.gravatar.com/avatar/'.md5(strtolower(trim($comment['mail']))).'?s=40'; ?>">
						<div class="words-content">
							<span><?php echo $comment['url'] ? '<a rel ="nofollow" href="'.$comment['url'].'">'.$comment['author'].'</a>' : $comment['author']; ?> </span>
							<span class="words-meta">
							<?php if($comment['parent']): ?>
								<?php $commentParent = Typecho_Widget::widget('Words_Action')->getcomments('single', $word['wid'], $comment['parent']); ?>
								回复 <?php echo $commentParent['author']; ?>
							<?php endif; ?>
								@<time datetime="<?php echo date('c', $comment['created']+Helper::options()->timezone); ?>"><?php echo date('Y-m-d H:i', $comment['created']+Helper::options()->timezone); ?></time>
							</span>：
							<?php echo $comment['content']; ?>
						</div>
						<p wid="<?php echo $word['wid']; ?>" cid="<?php echo $comment['cid'] ?>" class="words-button">回复</p>
						<div class="clear"></div>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="clear"></div>
		</div>
	<?php endforeach; ?>
	<div id="words-reply">
		<span class="words-comments-cancel">取消回复</span>
		<form method="post" id="words-reply-form" action="<?php Helper::options()->index('action/words?do=addComments'); ?>" role="form">
			<input id="words-reply-wid" type="hidden" name="wid">
			<input id="words-reply-parent" type="hidden" name="parent">
			<div id="comment-info">
				<p><input type="text" name="author" id="author" class="text" placeholder="<?php _e('称呼'); ?>" required></p>
				<p><input type="email" name="mail" id="mail" class="text" placeholder="<?php _e('邮箱'); ?>" ?>></p>
				<p><input type="url" name="url" id="url" class="text" placeholder="<?php _e('网站 http://'); ?>" ?>></p>
				<p><button type="submit" class="comment-submit"><?php _e('提交评论'); ?></button></p>
			</div>
			<div id="comment-content">
				<textarea name="content" id="textarea" class="textarea" placeholder="<?php _e('内容'); ?>" required></textarea>
			</div>
			<div class="clear"></div>
		</form>
	</div>
</div>