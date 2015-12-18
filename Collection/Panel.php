<?php
include 'common.php';
include 'header.php';
include 'menu.php';

$do = isset($request->do) ? $request->get('do') : 'manage';
$class = isset($request->class) ? $request->get('class') : '0';
$status = isset($request->status) ? $request->get('status') : 'do';
$status_trans = array(
	'all' => array('全部', '书籍', '动画', '音乐', '游戏', '广播', '影视'),
	'do' => array('进行', '在读', '在看', '在听', '在玩', '在听', '在看'),
	'collect' => array('完成', '读过', '看过', '听过', '玩过', '听过', '看过'),
	'wish' => array('计划', '想读', '想看', '想听', '想玩', '想听', '想看'),
	'on_hold' => array('搁置', '搁置', '搁置', '搁置', '搁置', '搁置', '搁置'),
	'dropped' => array('抛弃', '抛弃', '抛弃', '抛弃', '抛弃', '抛弃', '抛弃')
);
$progress_trans = array(
	'Collection' => array('收藏', '本篇', '特典'),
	'Series' => array('系列', '卷', '番外'),
	'Tankōbon' => array('单行本', '章', '节'),
	'TV' => array('TV', '本篇', '特典'),
	'OVA' => array('OVA', '本篇', '特典'),
	'EP' => array('EP', '本篇', '特典'),
	'Album' => array('Album', '本篇', '特典'),
	'Android' => array('Andriod', '本篇', '特典'),
	'PSV' => array('PSV', '奖杯', '收集'),
	'3DS' => array('3DS', '本篇', '特典'),
	'PC' => array('PC', '路线', '收集'),
	'RadioDrama' => array('广播剧', '本篇', '番外'),
	'Teleplay' => array('电视剧', '本篇', '特典'),
	'TalkShow' => array('脱口秀', '本篇', '特典'),
	'Movie' => array('电影', '本篇', '特典')
);
?>

<link rel="stylesheet" type="text/css" href="<?php $options->pluginUrl('Collection/template/stylesheet-common.css'); ?>">
<link rel="stylesheet" type="text/css" href="<?php $options->pluginUrl('Collection/template/stylesheet-panel.css'); ?>">
<div class="main">
	<div class="body container">
		<?php include 'page-title.php'; ?>
		<div class="colgroup typecho-page-main" role="main">
			<div class="col-mb-12">
				<?php if($do == 'manage'): ?>
					<ul class="typecho-option-tabs right">
						<?php foreach($status_trans as $key => $value): ?>
							<li <?php if($status == $key): ?>class="current"<?php endif; ?>><a href="<?php $options->adminUrl('extending.php?panel=Collection%2FPanel.php&class='.$class.'&status='.$key); ?>"><?php _e($status_trans[$key][$class]); ?></a></li>
						<?php endforeach; ?>
					</ul>
					<ul class="typecho-option-tabs clearfix">
						<?php foreach($status_trans['all'] as $key => $value): ?>
							<li<?php if($class == $key): ?> class="current"<?php endif; ?>><a href="<?php $options->adminUrl('extending.php?panel=Collection%2FPanel.php&class='.$key.'&status='.$status); ?>"><?php _e($value); ?></a></li>
						<?php endforeach; ?>
					</ul>
					<div class="col-mb-12 typecho-list" role="main">
						<?php $response = Typecho_Widget::widget('Collection_Action')->getCollection(); ?>
						<div class="typecho-list-operate clearfix">
							<form method="get">
								<div class="operate">
									<label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox" class="typecho-table-select-all" /></label>
									<div class="btn-group btn-drop">
										<button class="dropdown-toggle btn-s" type="button"><?php _e('<i class="sr-only">操作</i>选中项'); ?> <i class="i-caret-down"></i></button>
										<ul class="dropdown-menu">
											<?php foreach(array('do', 'collect', 'wish', 'on_hold', 'dropped') as $value): ?>
												<li><a lang="<?php _e('你确认要修改这些记录到'.$status_trans[$value][$class].'吗?'); ?>" href="<?php $options->index('/action/collection?do=editStatus&&status='.$value); ?>"><?php _e('修改到'.$status_trans[$value][$class]); ?></a></li>
											<?php endforeach; ?>
											<li><a lang="<?php _e('你确认要删除记录中的这些记录吗?'); ?>" href="<?php $options->index('/action/collection?do=editStatus&status=delete'); ?>"><?php _e('删除记录'); ?></a></li>
										</ul>
									</div>
								</div>
							</form>
							<?php if($response['result']): ?>
								<ul class="typecho-pager">
									<?php $response['nav']->render(_t('&laquo;'), _t('&raquo;')); ?>
								</ul>
							<?php endif; ?>
						</div>
						<form method="post" class="operate-form">
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
										<?php if($response['result']): ?>
											<?php foreach($response['list'] as $subject): ?>
												<tr id="subject-<?php echo $subject['id']; ?>" data-subject="<?php echo htmlspecialchars(json_encode($subject)); ?>">
													<td><input type="checkbox" name="id[]" value="<?php echo $subject['id']; ?>"></td>
													<?php
														echo '<td><div class="subject-image"><img src="';
														if(!$subject['image'])
															$options->pluginUrl('Collection/template/default_cover.jpg');
														elseif($subject['bangumi_id'])
															echo 'http://lain.bgm.tv/pic/cover/m/'.$subject['image'];
														else
															echo $options->plugin('Collection')->imageUrl ? $options->plugin('Collection')->imageUrl.'m/'.$subject['image'] : $subject['image'];
														echo '" width="100px"></div></td>';
													?>
													<td class="subject-meta">
														<div class="subject-name"><i class="subject_class-ico subject_class-<?php echo $subject['class']; ?>"></i><?php echo $subject['bangumi_id'] ? '<a href="http://bangumi.tv/subject/'.$subject['bangumi_id'].'">'.$subject['name'].'</a>' : $subject['name']; ?></div>
														<?php if($subject['name_cn']) echo '<div class="subject-name_cn">'.$subject['name_cn'].'</div>'; ?>
														<?php
															if(!is_null($subject['ep_count']) && !is_null($subject['ep_status']))
															{
																echo '<div id="subject-'.$subject['id'].'-ep">';
																echo '<label for="subject-'.$subject['id'].'-ep_progress">'._t('本篇进度').'</label>';
																echo '<div id="subject-'.$subject['id'].'-ep_progress" class="subject-progress"><div class="subject-progress-inner" style="color:white; width:'.($subject['ep_count'] ? $subject['ep_status']/$subject['ep_count']*100 : 50).'%"><small>'.$subject['ep_status'].' / '.($subject['ep_count'] ? $subject['ep_count'] : '??').'</small></div></div>';
																if(!$subject['ep_count'] || $subject['ep_count']>$subject['ep_status'])
																{
																	echo '<div class="hidden-by-mouse"><small><a href="#'.$subject['id'].'" rel="';
																	$options->index('/action/collection?do=plusEp&plus=ep');
																	echo '" class="subject-plus" id="subject-'.$subject['id'].'-ep_plus">ep.'.($subject['ep_status']+1).'已'.$status_trans['collect'][$subject['class']].'</a></small></div>';
																}
																echo '</div>';
															}
															if(!is_null($subject['sp_count']) && !is_null($subject['sp_status']))
															{
																echo '<div id="subject-'.$subject['id'].'-sp"'.(($subject['sp_count'] || $subject['sp_status']) ? '' : ' class="hidden"').'>';
																echo '<label for="subject-'.$subject['id'].'-sp_progress">'._t('特典进度').'</label>';
																echo '<div id="subject-'.$subject['id'].'-sp_progress" class="subject-progress"><div class="subject-progress-inner" style="color:white; width:'.($subject['sp_count'] ? $subject['sp_status']/$subject['sp_count']*100 : 50).'%"><small>'.$subject['sp_status'].' / '.($subject['sp_count'] ? $subject['sp_count'] : '??').'</small></div></div>';
																if(!$subject['sp_count'] || $subject['sp_count']>$subject['sp_status'])
																{
																	echo '<div class="hidden-by-mouse"><small><a href="#'.$subject['id'].'" rel="';
																	$options->index('/action/collection?do=plusEp&plus=sp');
																	echo '" class="subject-plus" id="subject-'.$subject['id'].'-sp_plus">sp.'.($subject['sp_status']+1).'已'.$status_trans['collect'][$subject['class']].'</a></small></div>';
																}
																echo '</div>';
															}
														?>
													</td>
													<td class="subject-review">
														<p class="subject-rate"><i>评价：</i><?php echo str_repeat('<span class="rate-star rate-star-rating"></span>', $subject['rate']); echo str_repeat('<span class="rate-star rate-star-blank"></span>', 10-$subject['rate']); ?></p>
														<p class="subject-tags"><i>标签：</i><?php echo $subject['tags'] ? $subject['tags'] : '无'; ?></p>
														<p class="subject-comment"><i>吐槽：</i><?php echo $subject['comment'] ? $subject['comment'] : '无'; ?></p>
														<p class="hidden-by-mouse"><a href="#<?php echo $subject['id']; ?>" rel="<?php $options->index('/action/collection?do=editSubject'); ?>" class="subject-edit"><?php _e('编辑'); ?></a></p>
													</td>
												</tr>
											<?php endforeach; ?>
										<?php else: ?>
											<tr><td colspan="6"><h6 class="typecho-list-table-title"><?php echo $response['message']; ?></h6></td></tr>
										<?php endif; ?>
									</tbody>
								</table>
							</div>
						</form>
						<div class="typecho-list-operate clearfix">
							<form method="get">
								<div class="operate">
									<label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox" class="typecho-table-select-all" /></label>
									<div class="btn-group btn-drop">
										<button class="dropdown-toggle btn-s" type="button"><?php _e('<i class="sr-only">操作</i>选中项'); ?> <i class="i-caret-down"></i></button>
										<ul class="dropdown-menu">
											<?php foreach(array('do', 'collect', 'wish', 'on_hold', 'dropped') as $value): ?>
												<li><a lang="<?php _e('你确认要修改这些记录到'.$status_trans[$value][$class].'吗?'); ?>" href="<?php $options->index('/action/collection?do=editStatus&status='.$value); ?>"><?php _e('修改到'.$status_trans[$value][$class]); ?></a></li>
											<?php endforeach; ?>
											<li><a lang="<?php _e('你确认要删除记录中的这些记录吗?'); ?>" href="<?php $options->index('/action/collection?do=editStatus&status=delete'); ?>"><?php _e('删除记录'); ?></a></li>
										</ul>
									</div>
								</div>
							</form>
							<?php if($response['result']): ?>
								<ul class="typecho-pager">
									<?php $response['nav']->render(_t('&laquo;'), _t('&raquo;')); ?>
								</ul>
							<?php endif; ?>
						</div>
					</div>
				<?php else: ?>
					<ul class="typecho-option-tabs right">
						<li<?php if($do == 'new'): ?> class="current"<?php endif; ?>><a href="<?php $options->adminUrl('extending.php?panel=Collection%2FPanel.php&do=new'); ?>">输入</a></li>
						<li<?php if($do == 'search'): ?> class="current"<?php endif; ?>><a href="<?php $options->adminUrl('extending.php?panel=Collection%2FPanel.php&do=search'); ?>">搜索</a></li>
					</ul>
					<?php if($do == 'new'): ?>
						<div class="col-mb-12 typecho-list" role="main">
						</div>
					<?php else: ?>
						<div class="col-mb-12 typecho-list" role="main">
							<?php $response = Typecho_Widget::widget('Collection_Action')->search(); ?>
							<div class="typecho-list-operate clearfix">
								<form method="get">
									<div class="operate">
										<label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox" class="typecho-table-select-all" /></label>
										<div class="btn-group btn-drop">
											<button class="dropdown-toggle btn-s" type="button"><?php _e('<i class="sr-only">操作</i>选中项'); ?> <i class="i-caret-down"></i></button>
											<ul class="dropdown-menu">
												<?php foreach(array('do', 'collect', 'wish', 'on_hold', 'dropped') as $value): ?>
													<li><a lang="<?php _e('你确认要添加这些记录到'.$status_trans[$value][$class].'吗?'); ?>" href="<?php $options->index('/action/collection?do=editStatus&status='.$value); ?>"><?php _e('添加到'.$status_trans[$value][$class]); ?></a></li>
												<?php endforeach; ?>
											</ul>
										</div>
										<a style="margin-left:5px;" href="<?php $options->index('/action/collection?do=getBangumi'); ?>">同步</a>
									</div>
									<div class="search" role="search">
										<a href="<?php $options->adminUrl('extending.php?panel=Collection%2FPanel.php'); ?>">返回</a>
										<input type="hidden" value="Collection/Panel.php" name="panel">
										<input type="hidden" value="search" name="do">
										<input type="text" class="text-s" placeholder="<?php _e('请输入关键字'); ?>" value="<?php echo htmlspecialchars($request->keywords); ?>"<?php if ('' == $request->keywords): ?> onclick="value='';name='keywords';" <?php else: ?> name="keywords"<?php endif; ?>>
										<select name="class">
											<?php foreach ($status_trans['all'] as $key => $value): ?>
												<option value="<?php echo $key; ?>"<?php if($request->get('class') == $key): ?> selected="true"<?php endif; ?>><?php echo $value; ?></option>
											<?php endforeach; ?>
										</select>
										<button type="submit" class="btn-s"><?php _e('搜索'); ?></button>
									</div>
								</form>
							</div>
							<form method="post" class="operate-form">
								<div class="typecho-table-wrap">
									<table class="typecho-list-table">
										<colgroup>
											<col width="20px">
											<col width="120px">
											<col width="180px">
											<col width="">
										</colgroup>
										<thead>
											<tr>
												<th></th>
												<th>封面</th>
												<th>名称</th>
												<th>简介</th>
											</tr>
										</thead>
										<tbody>
											<?php if($response['result']): ?>
												<?php foreach($response['list'] as $subject): ?>
													<tr>
														<td><input type="checkbox" name="subject_id[]" value="<?php echo $subject['id']; ?>"></td>
														<td><img src="<?php echo $subject['images']['medium']; ?>" width="100px"></td>
														<td><div><i class="subject_class-ico subject_class-<?php echo $subject['type']; ?>"></i><a href="<?php echo $subject['url']; ?>"><?php echo $subject['name']; ?></a></div><div><small><?php echo $subject['name_cn']; ?></small></div></td>
														<td><?php echo $subject['summary']; ?></td>
													</tr>
												<?php endforeach; ?>
											<?php else: ?>
												<tr><td colspan="4"><h6 class="typecho-list-table-title"><?php echo $response['message']; ?></h6></td></tr>
											<?php endif; ?>
										</tbody>
									</table>
								</div>
							</form>
							<div class="typecho-list-operate clearfix">
								<?php if($response['result']): ?>
									<ul class="typecho-pager">
										<?php $response['nav']->render(_t('&laquo;'), _t('&raquo;')); ?>
									</ul>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
include 'table-js.php';
?>
<script type="text/javascript">
$(document).ready(function () {
	$('.subject-plus').click(function(){
		var tr = $(this).parents('tr');
		var t = $(this);
		var id = tr.attr('id');
		var subject = tr.data('subject');
		$.post(t.attr('rel'), {"id": subject.id}, function (data) {
			if(data.result)
			{
				if(data.plus == 'ep')
				{
					subject.ep_status = data.ep_status;
					if(data.status == 'collect')
						subject.status = 'collect';
					else
						t.html('ep.'+(Number(data.ep_status)+1)+'已看过');
					tr.data('subject', subject);
					t.parent().parent().prev().html('<div class="subject-progress-inner" style="color:white; width:'+(subject.ep_count != '0' ? subject.ep_status/subject.ep_count*100 : 50)+'%"><small>'+subject.ep_status+' / '+(subject.ep_count != '0' ? subject.ep_count : '??')+'</small></div>');
					if(subject.ep_count != 0 && subject.ep_status == subject.ep_count)
						t.parent().parent().remove();
				}
				else
				{
					subject.sp_status = data.sp_status;
					if(data.status == 'collect')
						subject.status = 'collect';
					else
						t.html('sp.'+(Number(data.sp_status)+1)+'已看过');
					tr.data('subject', subject);
					t.parent().parent().prev().html('<div class="subject-progress-inner" style="color:white; width:'+(subject.sp_count != '0' ? subject.sp_status/subject.sp_count*100 : 50)+'%"><small>'+subject.sp_status+' / '+(subject.sp_count != '0' ? subject.sp_count : '??')+'</small></div>');
					if(subject.sp_count != 0 && subject.sp_status == subject.sp_count)
						t.parent().parent().remove();
				}
			} 
			else
				alert(data.message);
				
		});
	});

	$('.subject-edit').click(function () {
		var tr = $(this).parents('tr');
		var t = $(this);
		var id = tr.attr('id');
		var subject = tr.data('subject');
		tr.hide();
		var string = '<tr class="subject-edit">'
					+ '<td> </td>'
					+ '<td><form method="post" action="'+t.attr('rel')+'" class="subject-edit-content">'
						+ '<p><label for="' + id + '-image"><?php _e('封面'); ?></label>'
						+ '<textarea name="image" id="' + id + '-image" rows="3" class="w-100 mono"></textarea></p>'
						+ '<p><label for="' + id + '-class"><?php _e('种类'); ?></label><select id="' + id + '-class" name="class" class="w-100">'
							+ '<option value=1>书籍</option>'
							+ '<option value=2>动画</option>'
							+ '<option value=3>音乐</option>'
							+ '<option value=4>游戏</option>'
							+ '<option value=5>广播</option>'
							+ '<option value=6>影视</option>'
						+ '</select></p>'
						+ '<p><label for="' + id + '-type"><?php _e('类型'); ?></label><select id="' + id + '-type" name="type" class="w-100">'
						<?php
							foreach($progress_trans as $key => $value)
							{
								echo "+ '<option value=".$key.">".$value[0]."</option>'";
							}
						?>
						+ '</select></p>'
					+ '</form></td>'
					+ '<td><form method="post" action="'+t.attr('rel')+'" class="subject-edit-info">'
						+ '<p><label for="' + id + '-ep_status"><?php _e('原名'); ?></label><input class="text-s" type="text" id="' + id + '-name" name="name"></p>'
						+ '<p><label for="' + id + '-ep_status"><?php _e('译名'); ?></label><input class="text-s" type="text" id="' + id + '-name_cn" name="name_cn"></p>'
						+ '<p><label for="' + id + '-ep_status"><?php _e('本篇进度'); ?></label><input class="text-s w-100" id="' + id + '-ep_status" name="ep_status" type="text"></p>'
						+ '<p><label for="' + id + '-ep_count"><?php _e('总集数'); ?></label><input class="text-s w-100" type="text" name="ep_count" id="' + id + '-ep_count"></p>'
						+ '<p><label for="' + id + '-sp_status"><?php _e('特典进度'); ?></label><input class="text-s w-100" id="' + id + '-sp_status" name="sp_status" type="text"></p>'
						+ '<p><label for="' + id + '-sp_count"><?php _e('总集数'); ?></label><input class="text-s w-100" type="text" name="sp_count" id="' + id + '-sp_count"></p>'
					+ '</form></td>'
					+ '<td id="review-'+subject.subject_id+'"><form method="post" action="'+t.attr('rel')+'" class="subject-edit-content">'
						+ '<p><label for="' + id + '-rate"><?php _e('评价'); ?></label>'
						+ '<input class="text-s w-100" type="text" name="rate" id="' + id + '-rate"></p>'
						+ '<p><label for="' + id + '-tags"><?php _e('标签'); ?></label>'
						+ '<input class="text-s w-100" type="text" name="tags" id="' + id + '-tags"></p>'
						+ '<p><label for="' + id + '-comment"><?php _e('吐槽'); ?></label>'
						+ '<textarea name="comment" id="' + id + '-comment" rows="6" class="w-100 mono"></textarea></p>'
						+ '<p><button type="submit" class="btn-s primary"><?php _e('提交'); ?></button> '
						+ '<button type="button" class="btn-s cancel"><?php _e('取消'); ?></button></p>'
					+ '</form></td>'
				+ '</tr>';
		
		var edit = $(string).data('id', id).data('subject', subject).insertAfter(tr);

		$('textarea[name=image]', edit).val(subject.image);
		$('select[name=class]', edit).val(subject.class);
		$('select[name=type]', edit).val(subject.type);
		$('input[name=name]', edit).val(subject.name);
		$('input[name=name_cn]', edit).val(subject.name_cn);
		$('input[name=ep_status]', edit).val(subject.ep_status);
		$('input[name=ep_count]', edit).val(subject.ep_count);
		$('input[name=sp_status]', edit).val(subject.sp_status);
		$('input[name=sp_count]', edit).val(subject.sp_count);
		$('input[name=rate]', edit).val(subject.rate);
		$('input[name=tags]', edit).val(subject.tags);
		$('textarea[name=comment]', edit).val(subject.comment).focus();

		$('.cancel', edit).click(function () {
			var tr = $(this).parents('tr');

			$('#' + tr.data('id')).show();
			tr.remove();
		});

		$('form', edit).submit(function () {
			var t = $(this), tr = t.parents('tr'),
				oldTr = $('#' + tr.data('id')),
				subject = oldTr.data('subject');

			$('form', tr).each(function () {
				var items  = $(this).serializeArray();

				for (var i = 0; i < items.length; i ++) {
					var item = items[i];
					subject[item.name] = item.value;
				}
			});

			oldTr.data('subject', subject);

			$.post(t.attr('action'), subject, function (data) {
				if(data.status)
				{
					$('.subject-image', oldTr).html('<img src="'+(subject.image ? (subject.bangumi_id ? 'http://lain.bgm.tv/pic/cover/m/'+subject.image : ('<?php echo $options->plugin('Collection')->imageUrl ? $options->plugin('Collection')->imageUrl.'m/' : ''; ?>'+subject.image)) : '<?php $options->pluginUrl('Collection/template/default_cover.jpg'); ?>')+'" width="100px">');
					$('.subject-name', oldTr).html('<i class="subject_class-ico subject_class-'+subject.class+'"></i>'+(subject.bangumi_id ? '<a href="http://bangumi.tv/subject/'+subject.bangumi_id+'">'+subject.name+'</a>' : subject.name)+'</div>');
					$('.subject-name_cn', oldTr).html(subject.name_cn);
					if(data.status == 'collect')
						$('.subject-plus', oldTr).parents('div.hidden-by-mouse').remove();
					else
					{
						$('#subject-'+subject.id+'-ep_plus', oldTr).html('ep.'+(parseInt(subject.ep_status)+1)+'已看过');
						$('#subject-'+subject.id+'-sp_plus', oldTr).html('ep.'+(parseInt(subject.sp_status)+1)+'已看过');
					}
					$('#subject-'+subject.id+'-ep_progress', oldTr).html('<div class="subject-progress-inner" style="color:white; width:'+(subject.ep_count != '0' ? subject.ep_status/subject.ep_count*100 : 50)+'%"><small>'+subject.ep_status+' / '+(subject.ep_count != '0' ? subject.ep_count : '??')+'</small></div>');
					$('#subject-'+subject.id+'-sp_progress', oldTr).html('<div class="subject-progress-inner" style="color:white; width:'+(subject.sp_count != '0' ? subject.sp_status/subject.sp_count*100 : 50)+'%"><small>'+subject.sp_status+' / '+(subject.sp_count != '0' ? subject.sp_count : '??')+'</small></div>');
					if(subject.sp_count || subject.sp_status)
						$('#subject-'+subject.id+'-sp').removeClass('hidden');
					else
						$('#subject-'+subject.id+'-sp').addClass('hidden');
					$('.subject-rate', oldTr).html('<i>评价：</i>'+ '<span class="rate-star rate-star-rating"></span>'.repeat(subject.rate)+'<span class="rate-star rate-star-blank"></span>'.repeat(10-subject.rate));
					$('.subject-tags', oldTr).html('<i>标签：</i>'+(subject.tags ? subject.tags : '无'));
					$('.subject-comment', oldTr).html('<i>吐槽：</i>'+(subject.comment ? subject.comment : '无'));
					$(oldTr).effect('highlight');
				}
				else
					alert(data.message);
			}, 'json');
			
			oldTr.show();
			tr.remove();

			return false;
		});

		return false;
	});
});
</script>
<?php
include 'footer.php';
?>

