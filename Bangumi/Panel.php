<?php
include 'common.php';
include 'header.php';
include 'menu.php';

$type = isset($request->type) ? $request->get('type') : 'do';
?>

<link rel="stylesheet" type="text/css" href="<?php $options->pluginUrl('Bangumi/template/bangumi.css'); ?>">
<div class="main">
	<div class="body container">
		<?php include 'page-title.php'; ?>
		<div class="colgroup typecho-page-main" role="main">
			<div class="col-mb-12">
				<ul class="typecho-option-tabs clearfix">
					<li <?php if($type == 'do'): ?>class="current"<?php endif; ?>><a href="<?php $options->adminUrl('extending.php?panel=Bangumi%2FPanel.php'); ?>"><?php _e('在看'); ?></a></li>
					<li <?php if($type == 'collect'): ?>class="current"<?php endif; ?>><a href="<?php $options->adminUrl('extending.php?panel=Bangumi%2FPanel.php&type=collect'); ?>"><?php _e('看过'); ?></a></li>
					<li <?php if($type == 'other'): ?>class="current"<?php endif; ?>><a href="<?php $options->adminUrl('extending.php?panel=Bangumi%2FPanel.php&type=other'); ?>"><?php _e('其它'); ?></a></li>
					<li <?php if($type == 'search'): ?>class="current"<?php endif; ?>><a href="<?php $options->adminUrl('extending.php?panel=Bangumi%2FPanel.php&type=search'); ?>"><?php _e('搜索'); ?></a></li>
				</ul>
				<div class="col-mb-12 typecho-list" role="main">
					<?php if($type != 'search'): ?>
						<?php $result = Typecho_Widget::widget('Bangumi_Action')->getCollection(); ?>
						<form method="get">
							<div class="typecho-list-operate clearfix">
								<div class="operate">
									<label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox" class="typecho-table-select-all" /></label>
									<div class="btn-group btn-drop">
										<button class="dropdown-toggle btn-s" type="button"><?php _e('<i class="sr-only">操作</i>选中项'); ?> <i class="i-caret-down"></i></button>
										<ul class="dropdown-menu">
											<li><a lang="<?php _e('你确认要修改这些记录为在看吗?'); ?>" href="<?php $options->index('/action/bangumi?do=editCollection&collection=do'); ?>"><?php _e('修改为在看'); ?></a></li>
											<li><a lang="<?php _e('你确认要修改这些记录为看过吗?'); ?>" href="<?php $options->index('/action/bangumi?do=editCollection&collection=collect'); ?>"><?php _e('修改为看过'); ?></a></li>
											<li><a lang="<?php _e('你确认要修改这些记录为其它吗?'); ?>" href="<?php $options->index('/action/bangumi?do=editCollection&collection=collect'); ?>"><?php _e('修改为其它'); ?></a></li>
											<li><a lang="<?php _e('你确认要删除记录中的这些记录吗?'); ?>" href="<?php $options->index('/action/bangumi?do=editCollection&collection=delete'); ?>"><?php _e('删除记录'); ?></a></li>
										</ul>
									</div>
								</div>
							</div>
							<div class="typecho-table-wrap">
								<table class="typecho-list-table">
									<colgroup>
										<col width="20px">
										<col width="120px">
										<col width="200px">
										<col width="">
									</colgroup>
									<thead>
										<tr>
											<th></th>
											<th>封面</th>
											<th>名称</th>
											<th>简评</th>
										</tr>
									</thead>
									<tbody>
										<?php if($result['status']): ?>
											<?php foreach($result['list'] as $bangumi): ?>
												<tr id="bangumi-<?php echo $bangumi['subject_id']; ?>">
													<td><input type="checkbox" name="id[]" value="<?php echo $bangumi['id']; ?>"></td>
													<td><img src="<?php echo $bangumi['image']; ?>" width="100px"></td>
													<td>
														<div><i class="ico_subject_type subject_type_<?php echo $bangumi['type']; ?>"></i><a href="http://bangumi.tv/subject/<?php echo $bangumi['subject_id']; ?>"><?php echo $bangumi['name']; ?></a></div>
														<div><small><?php echo $bangumi['name_cn']; ?></small></div>
														<div class="bangumi-progress">
															<?php
																if($bangumi['eps'] == 0){
																	$width = 100;
																}
																else{
																	$width = $bangumi['ep_status']/$bangumi['eps']*100;
																}
															?>
															<div class="bangumi-progress-inner" style="color: <?php echo ($width>10?'#FFF':'#000'); ?>; width:<?php echo $width; ?>%"><small><?php echo $bangumi['ep_status']; ?>/<?php echo $bangumi['eps']; ?></small></div>
														</div>
													</td>
													<td valign="top">
														<p><i><?php echo $bangumi['tags']; ?></i></p>
														<p><?php echo $bangumi['comment']; ?></p>
														<p>添加编辑按钮</p>
													</td>
												</tr>
											<?php endforeach; ?>
										<?php else: ?>
											<tr><td colspan="6"><h6 class="typecho-list-table-title"><?php echo $result['message']; ?></h6></td></tr>
										<?php endif; ?>
									</tbody>
								</table>
							</div>
							<div class="typecho-list-operate clearfix">
								<?php if($result['status']): ?>
									<ul class="typecho-pager">
										<?php $result['nav']->render(_t('&laquo;'), _t('&raquo;')); ?>
									</ul>
								<?php endif; ?>
							</div>
						</form>
					<?php else: ?>
						<form method="get" style="margin-top: 20px;" class="clearfix">
							<div class="search" role="search">
								<input type="hidden" value="Bangumi/Panel.php" name="panel">
								<input type="hidden" value="search" name="type">
								<input type="text" class="text-s" placeholder="<?php _e('请输入关键字'); ?>" value="<?php echo htmlspecialchars($request->keywords); ?>" name="keywords">
								<button type="submit" class="btn-s"><?php _e('搜索'); ?></button>
							</div>
						</form>
						<?php
							if($request->get('keywords'))
								$result = Typecho_Widget::widget('Bangumi_Action')->search();
							else
								$result = array('status' => false, 'message' => '无搜索结果');
						?>
						<form method="get">
							<div class="typecho-list-operate clearfix">
								<div class="operate">
									<label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox" class="typecho-table-select-all" /></label>
									<div class="btn-group btn-drop">
										<button class="dropdown-toggle btn-s" type="button"><?php _e('<i class="sr-only">操作</i>选中项'); ?> <i class="i-caret-down"></i></button>
										<ul class="dropdown-menu">
											<li><a lang="<?php _e('你确认要添加这些记录为在看吗?'); ?>" href="<?php $options->index('/action/bangumi?do=editCollection&collection=do'); ?>"><?php _e('添加为在看'); ?></a></li>
											<li><a lang="<?php _e('你确认要添加这些记录为看过吗?'); ?>" href="<?php $options->index('/action/bangumi?do=editCollection&collection=collect'); ?>"><?php _e('添加为看过'); ?></a></li>
										</ul>
									</div>
								</div>
								<?php if($result['status']): ?>
									<ul class="typecho-pager">
										<?php $result['nav']->render(_t('&laquo;'), _t('&raquo;')); ?>
									</ul>
								<?php endif; ?>
							</div>
							<div class="typecho-table-wrap">
								<table class="typecho-list-table">
									<colgroup>
										<col width="20px">
										<col width="120px">
										<col width="180px">
										<col width="">
										<col width="120px">
									</colgroup>
									<thead>
										<tr>
											<th></th>
											<th>封面</th>
											<th>名称</th>
											<th>简介</th>
											<th>收藏</th>
										</tr>
									</thead>
									<tbody>
										<?php if($result['status']): ?>
											<?php foreach($result['list'] as $bangumi): ?>
												<tr id="bangumi-<?php echo $bangumi['id']; ?>">
													<td><input type="checkbox" name="id[]" value="<?php echo $bangumi['id']; ?>"></td>
													<td><img src="<?php echo $bangumi['images']['medium']; ?>" width="100px"></td>
													<td><div><i class="ico_subject_type subject_type_<?php echo $bangumi['type']; ?>"></i><a href="<?php echo $bangumi['url']; ?>"><?php echo $bangumi['name']; ?></a></div><div><small><?php echo $bangumi['name_cn']; ?></small></div></td>
													<td><?php echo $bangumi['summary']; ?></td>
													<td><?php
														foreach($bangumi['collection'] as $collectStatus => $collectNum)
															echo '<div>'.$collectStatus.': '.$collectNum.'</div>';
													?></td>
												</tr>
											<?php endforeach; ?>
										<?php else: ?>
											<tr><td colspan="5"><h6 class="typecho-list-table-title"><?php echo $result['message']; ?></h6></td></tr>
										<?php endif; ?>
									</tbody>
								</table>
							</div>
						</form>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
include 'table-js.php';
include 'footer.php';
?>