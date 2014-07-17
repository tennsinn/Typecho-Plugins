<?php
include 'common.php';
include 'header.php';
include 'menu.php';

$page = isset($request->page) ? $request->get('page') : '1';
$type = isset($request->type) ? $request->get('type') : 'words';
?>
<link rel="stylesheet" type="text/css" media="all" href="<?php $options->pluginUrl('/Words/egg/egg.css'); ?>">
<div class="main">
	<div class="body container">
		<?php include 'page-title.php'; ?>
		<div class="colgroup typecho-page-main" role="main">
			<div class="col-mb-12">
				<ul class="typecho-option-tabs clearfix">
					<li <?php if($type != 'comments'): ?>class="current"<?php endif; ?>><a href="<?php $options->adminUrl('extending.php?panel=Words%2FPanel.php'); ?>"><?php _e('碎语'); ?></a></li>
					<li <?php if($type == 'comments'): ?>class="current"<?php endif; ?>><a href="<?php $options->adminUrl('extending.php?panel=Words%2FPanel.php&type=comments'); ?>"><?php _e('评论'); ?></a></li>
				</ul>
			</div>
			<?php if($type != 'comments'): ?>
				<div class="col-mb-12" role="form">
					<?php if($type == 'edit'): ?>
						<?php $word = Typecho_Widget::widget('Words_Action')->getWords('single'); ?>
						<h3>编辑碎语 <span>#<?php echo $word['wid']; ?></span></h3>
						<form method="post" name="edit_words" action="<?php $options->index('/action/words?do=editWords') ?>">
							<input type="hidden" name="wid" value="<?php echo $word['wid']; ?>" required>
							<table width="100%">
								<colgroup>
									<col width="60px">
									<col width="">
									<col width="100px">
								</colgroup>
								<thead style="text-align:left;">
									<th>表情</th>
									<th>碎语</th>
									<th></th>
								</thead>
								<tbody style="text-align:center;"><tr>
									<td><select name="expression" id="dropdown_expression" class="egg_imagedropdown">
								<?php
									for($i=1; $i<90; $i++)
									{
										echo '<option';
										if(isset($request->wid) && 'onion-'.$i.'.gif' == $word['expression'])
											echo ' selected';
										echo ' value="onion-'.$i.'.gif">';
										echo $options->pluginUrl('/Words/expression/onion-'.$i.'.gif');
										echo '</option>';
									}
								?>
							</select></td>
									<td><textarea style="width:100%;" name="content"><?php echo isset($request->wid) ? $word['content'] : ''; ?></textarea></td>
									<td><button class="primary" type="submit">添加</button></td>
								</tr></tbody>
							</table>
						</form>
					<?php elseif($type == 'new'): ?>
						<h3>新增碎语</h3>
						<form method="post" name="add_words" action="<?php $options->index('/action/words?do=addWords') ?>">
							<table width="100%">
								<colgroup>
									<col width="60px">
									<col width="">
									<col width="100px">
								</colgroup>
								<thead style="text-align:left;">
									<th>表情</th>
									<th>碎语</th>
									<th></th>
								</thead>
								<tbody style="text-align:center;"><tr>
									<td><select name="expression" id="dropdown_expression" class="egg_imagedropdown">
								<?php
									for($i=1; $i<90; $i++)
									{
										echo '<option value="onion-'.$i.'.gif">';
										echo $options->pluginUrl('/Words/expression/onion-'.$i.'.gif');
										echo '</option>';
									}
								?>
							</select></td>
									<td><textarea style="width:100%;" name="content"><?php echo isset($request->wid) ? $word['content'] : ''; ?></textarea></td>
									<td><button class="primary" type="submit">添加</button></td>
								</tr></tbody>
							</table>
						</form>
					<?php endif; ?>
				</div>
				<div class="col-mb-12" role="main">
					<?php $wordsPage = Typecho_Widget::widget('Words_Action')->getWords(); ?>
					<form method="post" name="manage_words" class="operate-form" action="<?php $options->index('/action/words?do=delWords') ?>">
						<div class="typecho-list-operate clearfix">
							<div class="operate">
								<label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox" class="typecho-table-select-all" /></label>
								<div class="btn-group btn-drop">
									<button class="dropdown-toggle btn-s" type="button"><?php _e('<i class="sr-only">操作</i>选中项'); ?> <i class="i-caret-down"></i></button>
									<ul class="dropdown-menu">
										<li><a lang="<?php _e('你确认要删除这些碎语吗?'); ?>" href="<?php $options->index('/action/words?do=delWords'); ?>"><?php _e('删除'); ?></a></li>
									</ul>
								</div>
							</div>
							<ul class="typecho-pager">
								<?php $wordsPage['nav']->render(_t('&laquo;'), _t('&raquo;')); ?>
							</ul>
						</div>
						<div class="typecho-table-wrap">
							<table class="typecho-list-table">
								<colgroup>
									<col width="20px">
									<col width="60px">
									<col width="60px">
									<col width="">
									<col width="20%">
									<col width="60px">
								</colgroup>
								<thead>
									<tr>
										<th></th>
										<th></th>
										<th>表情</th>
										<th>内容</th>
										<th>日期</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach($wordsPage['words'] as $word): ?>
										<tr id="words-<?php echo $word['wid']; ?>">
											<td><input type="checkbox" name="wid[]" value="<?php echo $word['wid']; ?>"></td>
											<td><a href="<?php $options->adminUrl('extending.php?panel=Words%2FPanel.php&type=comments&wid='.$word['wid']); ?>" class="balloon-button size-<?php echo Typecho_Common::splitByCount($word['commentsNum'], 1, 10, 20, 50, 100); ?>"><?php echo $word['commentsNum']; ?></a></td>
											<td><img src="<?php $options->pluginUrl('/Words/expression/'.$word['expression']); ?>" width="50px"></td>
											<td><?php echo $word['content']; ?></td>
											<td><?php echo date('Y-m-d', $word['created']+Helper::options()->timezone); ?></td>
											<td><a href="<?php $options->adminUrl('extending.php?panel=Words%2FPanel.php&type=edit&page='.$page.'&wid='.$word['wid']); ?>">#<?php echo $word['wid']; ?></a></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</form>
				</div>
			<?php else: ?>
				<?php $commentsPage = Typecho_Widget::widget('Words_Action')->getComments(); ?>
				<div class="col-mb-12" role="main">
					<form method="post" name="manage_words_comments" class="operate-form">
						<div class="typecho-list-operate clearfix">
							<div class="operate">
								<label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox" class="typecho-table-select-all" /></label>
								<div class="btn-group btn-drop">
									<button class="dropdown-toggle btn-s" type="button"><?php _e('<i class="sr-only">操作</i>选中项'); ?> <i class="i-caret-down"></i></button>
									<ul class="dropdown-menu">
										<li><a lang="<?php _e('你确认要删除这些碎语吗?'); ?>" href="<?php $options->index('/action/words?do=delComments'); ?>"><?php _e('删除'); ?></a></li>
									</ul>
								</div>  
							</div>
							<ul class="typecho-pager">
								<?php $commentsPage['nav']->render(_t('&laquo;'), _t('&raquo;')); ?>
							</ul>
						</div>
						
						<div class="typecho-table-wrap">
							<table class="typecho-list-table">
								<colgroup>
									<col width="3%">
									<col width="6%">
									<col width="20%">
									<col width="71%">
								</colgroup>
								<thead>
									<tr>
										<th></th>
										<th>作者</th>
										<th></th>
										<th>内容</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach($commentsPage['comments'] as $comment): ?>
										<tr id="words-comments-<?php echo $comment['cid']; ?>">
											<td><input type="checkbox" name="cid[]" value="<?php echo $comment['cid']; ?>"></td>
											<td><div class="comment-avatar">
												<img src="<?php echo 'http://www.gravatar.com/avatar/'.md5(strtolower(trim($comment['mail']))).'?s=40'; ?>">
											</div></td>
											<td><div class="comment-meta">
												<strong class="comment-author"><?php echo $comment['author']; ?></strong>
												<?php if($comment['mail']): ?>
													<br><span><a href="mailto:<?php echo $comment['mail']; ?>"><?php echo$comment['mail']; ?></a></span>
												<?php endif; ?>
												<?php if($comment['ip']): ?>
													<br><span><?php echo $comment['ip']; ?></span>
												<?php endif; ?>
											</div></td>
											<td>
												<div class="comment-date"><?php echo date('Y-m-d', $comment['created']+Helper::options()->timezone); ?> 于 <a href="<?php $options->adminUrl('extending.php?panel=Words%2FPanel.php&wid='.$comment['wid'].'#words-'.$comment['wid']); ?>">#<?php echo $comment['wid']; ?></a></div>
												<div class="comment-content"><?php echo $comment['content']; ?></div>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</form>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>



<?php
include 'copyright.php';
include 'common-js.php';
include 'table-js.php';
?>
<script type="text/javascript" src="<?php $options->pluginUrl('/Words/egg/egg.js'); ?>"></script>
<script>
	$(function(){
		$('.egg_imagedropdown').EggImageDropdown({lock:'width', width:50, dropdownWidth:60, border:false, z:1});
	})
</script>
<?php
include 'footer.php';
?>