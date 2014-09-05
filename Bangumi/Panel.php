<?php
include 'common.php';
include 'header.php';
include 'menu.php';

$do = isset($request->do) ? $request->get('do') : 'manage';
$type = isset($request->type) ? $request->get('type') : '0';
$collection = isset($request->collection) ? $request->get('collection') : 'all';
$collection_status = array('all', 'do', 'collect', 'wish', 'on_hold', 'dropped');
$collection_trans = array('0' => array('全部', 'do', 'collect', 'wish', 'on_hold', 'dropped'), '1' => array('书籍', '在读', '读过', '想读', '搁置', '抛弃'), '2' => array('动画', '在看', '看过', '想看', '搁置', '抛弃'), '3' => array('音乐', '在听', '听过', '想听', '搁置', '抛弃'), '4' => array('游戏', '在玩', '玩过', '想玩', '搁置', '抛弃'), '6' => array('三次元', '在看', '看过', '想看', '搁置', '抛弃'));
?>

<link rel="stylesheet" type="text/css" href="<?php $options->pluginUrl('Bangumi/template/bangumi.css'); ?>">
<div class="main">
	<div class="body container">
		<?php include 'page-title.php'; ?>
		<div class="colgroup typecho-page-main" role="main">
			<div class="col-mb-12">
				<?php if($do == 'manage'): ?>
					<ul class="typecho-option-tabs right">
						<?php foreach($collection_status as $key => $value): ?>
							<li <?php if($collection == $value): ?>class="current"<?php endif; ?>><a href="<?php $options->adminUrl('extending.php?panel=Bangumi%2FPanel.php&type='.$type.'&collection='.$value); ?>"><?php _e($collection_trans[$type][$key]); ?></a></li>
						<?php endforeach; ?>
					</ul>
					<ul class="typecho-option-tabs clearfix">
						<?php foreach($collection_trans as $key => $value): ?>
							<li <?php if($type == $key): ?>class="current"<?php endif; ?>><a href="<?php $options->adminUrl('extending.php?panel=Bangumi%2FPanel.php&type='.$key.'&collection='.$collection); ?>"><?php _e($value[0]); ?></a></li>
						<?php endforeach; ?>
					</ul>
					<div class="col-mb-12 typecho-list" role="main">
						<?php $result = Typecho_Widget::widget('Bangumi_Action')->getCollection(); ?>
						<div class="typecho-list-operate clearfix">
							<form method="get">
								<div class="operate">
									<label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox" class="typecho-table-select-all" /></label>
									<div class="btn-group btn-drop">
										<button class="dropdown-toggle btn-s" type="button"><?php _e('<i class="sr-only">操作</i>选中项'); ?> <i class="i-caret-down"></i></button>
										<ul class="dropdown-menu">
											<?php for($i=1; $i<6; $i++): ?>
												<li><a lang="<?php _e('你确认要添加这些记录到'.$collection_trans[$type][$i].'吗?'); ?>" href="<?php $options->index('/action/bangumi?do=editCollection&type='.$type.'&collection='.$collection_trans['0'][$i]); ?>"><?php _e('添加到'.$collection_trans[$type][$i]); ?></a></li>
											<?php endfor; ?>
											<li><a lang="<?php _e('你确认要删除记录中的这些记录吗?'); ?>" href="<?php $options->index('/action/bangumi?do=editCollection&collection=delete'); ?>"><?php _e('删除记录'); ?></a></li>
										</ul>
									</div>
								</div>
							</form>
							<?php if($result['status']): ?>
								<ul class="typecho-pager">
									<?php $result['nav']->render(_t('&laquo;'), _t('&raquo;')); ?>
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
										<?php if($result['status']): ?>
											<?php foreach($result['list'] as $bangumi): ?>
												<tr id="bangumi-<?php echo $bangumi['id']; ?>" data-subject="<?php echo htmlspecialchars(json_encode($bangumi)); ?>">
													<td><input type="checkbox" name="subject_id[]" value="<?php echo $bangumi['subject_id']; ?>"></td>
													<td><img src="<?php echo $bangumi['image']; ?>" width="100px"></td>
													<td class="subject-meta">
														<div><i class="subject_type-ico subject_type-<?php echo $bangumi['type']; ?>"></i><a href="http://bangumi.tv/subject/<?php echo $bangumi['subject_id']; ?>"><?php echo $bangumi['name']; ?></a></div>
														<div><small><?php echo $bangumi['name_cn']; ?></small></div>
														<?php if($bangumi['type'] == 2 || $bangumi['type'] == 6): ?>
															<div class="bangumi-progress">
																<div class="bangumi-progress-inner" style="color:white; width:<?php echo $bangumi['eps'] ? $bangumi['ep_status']/$bangumi['eps']*100 : 100; ?>%"><small><?php echo $bangumi['ep_status']; ?> / <?php echo $bangumi['eps'] ? $bangumi['eps'] : '??'; ?></small></div>
															</div>
															<?php if($bangumi['collection'] != 'collect' && ($bangumi['type'] == 2 || $bangumi['type'] == 6)): ?>
																<div class="hidden-by-mouse"><small><a href="#<?php echo $bangumi['subject_id']; ?>" rel="<?php $options->index('/action/bangumi?do=epplus'); ?>" class="subject-ep-plus"><?php _e('ep.'.($bangumi['ep_status']+1).'已看过'); ?></a></small></div>
															<?php endif; ?>
														<?php endif; ?>
													</td>
													<td class="subject-review">
														<p class="subject-interest_rate">评价：<?php echo str_repeat('<span class="interest_rate-star interest_rate-star-rating"></span>', $bangumi['interest_rate']); echo str_repeat('<span class="interest_rate-star interest_rate-star-blank"></span>', 10-$bangumi['interest_rate']); ?></p>
														<p class="subject-tags">标签：<?php echo $bangumi['tags'] ? $bangumi['tags'] : '<i>无</i>'; ?></p>
														<p class="subject-comment">吐槽：<?php echo $bangumi['comment'] ? $bangumi['comment'] : '<i>无</i>'; ?></p>
														<p class="hidden-by-mouse"><a href="#<?php echo $bangumi['subject_id']; ?>" rel="<?php $options->index('/action/bangumi?do=editSubject'); ?>" class="subject-edit"><?php _e('编辑'); ?></a></p>
													</td>
												</tr>
											<?php endforeach; ?>
										<?php else: ?>
											<tr><td colspan="6"><h6 class="typecho-list-table-title"><?php echo $result['message']; ?></h6></td></tr>
										<?php endif; ?>
									</tbody>
								</table>
							</div>
						</form>
				<?php else: ?>
					<div class="col-mb-12 typecho-list" role="main">
						<?php $result = Typecho_Widget::widget('Bangumi_Action')->search(); ?>
						<div class="typecho-list-operate clearfix">
							<form method="get">
								<div class="operate">
									<label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox" class="typecho-table-select-all" /></label>
									<div class="btn-group btn-drop">
										<button class="dropdown-toggle btn-s" type="button"><?php _e('<i class="sr-only">操作</i>选中项'); ?> <i class="i-caret-down"></i></button>
										<ul class="dropdown-menu">
											<?php for($i=1; $i<6; $i++): ?>
												<li><a lang="<?php _e('你确认要添加这些记录到'.$collection_trans[$type][$i].'吗?'); ?>" href="<?php $options->index('/action/bangumi?do=editCollection&collection='.$collection_trans['0'][$i]); ?>"><?php _e('添加到'.$collection_trans[$type][$i]); ?></a></li>
											<?php endfor; ?>
										</ul>
									</div>
									<a style="margin-left:5px;" href="<?php $options->index('/action/bangumi?do=syncBangumi'); ?>">同步</a>
								</div>
								<div class="search" role="search">
									<a href="<?php $options->adminUrl('extending.php?panel=Bangumi%2FPanel.php'); ?>">返回</a>
									<input type="hidden" value="Bangumi/Panel.php" name="panel">
									<input type="hidden" value="new" name="do">
									<input type="text" class="text-s" placeholder="<?php _e('请输入关键字'); ?>" value="<?php echo htmlspecialchars($request->keywords); ?>"<?php if ('' == $request->keywords): ?> onclick="value='';name='keywords';" <?php else: ?> name="keywords"<?php endif; ?>>
									<select name="type">
										<option value="0"><?php _e('全部'); ?></option>
										<option value="1"<?php if($request->get('type') == '1'): ?> selected="true"<?php endif; ?>><?php _e('书籍') ?></option>
										<option value="2"<?php if($request->get('type') == '2'): ?> selected="true"<?php endif; ?>><?php _e('动漫') ?></option>
										<option value="3"<?php if($request->get('type') == '3'): ?> selected="true"<?php endif; ?>><?php _e('音乐') ?></option>
										<option value="4"<?php if($request->get('type') == '4'): ?> selected="true"<?php endif; ?>><?php _e('游戏') ?></option>
										<option value="6"<?php if($request->get('type') == '6'): ?> selected="true"<?php endif; ?>><?php _e('三次元') ?></option>
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
												<tr>
													<td><input type="checkbox" name="subject_id[]" value="<?php echo $bangumi['id']; ?>"></td>
													<td><img src="<?php echo $bangumi['images']['medium']; ?>" width="100px"></td>
													<td><div><i class="subject_type-ico subject_type-<?php echo $bangumi['type']; ?>"></i><a href="<?php echo $bangumi['url']; ?>"><?php echo $bangumi['name']; ?></a></div><div><small><?php echo $bangumi['name_cn']; ?></small></div></td>
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
						<div class="typecho-list-operate clearfix">
							<?php if($result['status']): ?>
								<ul class="typecho-pager">
									<?php $result['nav']->render(_t('&laquo;'), _t('&raquo;')); ?>
								</ul>
							<?php endif; ?>
						</div>
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
?>
<script type="text/javascript">
$(document).ready(function () {
	$('.subject-ep-plus').click(function(){
		var tr = $(this).parents('tr');
		var t = $(this);
		var id = tr.attr('id');
		var subject = tr.data('subject');
		$.post(t.attr('rel'), {"subject_id": subject.subject_id}, function (data) {
			if(data.status)
			{
				subject.ep_status = data.ep_status;
				tr.data('subject', subject);
				$('.bangumi-progress', tr).html('<div class="bangumi-progress-inner" style="color:white; width:'+(subject.eps != '0' ? subject.ep_status/subject.eps*100 : 100)+'%"><small>'+subject.ep_status+' / '+(subject.eps != '0' ? subject.eps : '??')+'</small></div>');
				if(data.collection=='collect')
					t.parents('div.hidden-by-mouse').remove();
				else
					t.html('ep.'+(data.ep_status+1)+'已看过');
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

		var edit = $('<tr class="subject-edit">'
						+ '<td> </td>'
						+ '<td><img width="100px" src="'+subject.image+'"></td>'
						+ '<td><div><i class="subject_type-ico subject_type-'+subject.type+'"></i><a href="http://bangumi.tv/subject/'+subject.subject_id+'">'+subject.name+'</a></div><div><small>'+subject.name_cn+'</small></div>'
						+ ((subject.type==2 || subject.type==6) ? (subject.collection == 'collect' ? '<div class="bangumi-progress"><div class="bangumi-progress-inner" style="color:white; width:'+(subject.eps != '0' ? subject.ep_status/subject.eps*100 : 100)+'%"><small>'+subject.ep_status+' / '+(subject.eps != '0' ? subject.eps : '??')+'</small></div></div>' : '<form method="post" action="'+t.attr('rel')+'" class="subject-edit-info">'
						+ '<p><label for="' + id + '-ep_status"><?php _e('收视进度'); ?></label><input class="text-s w-100" id="'
						+ id + '-ep_status" name="ep_status" type="text" required></p>'
						+ '<p><label for="' + id + '-eps"><?php _e('总集数'); ?></label>'
						+ '<input class="text-s w-100" type="text" name="eps" id="' + id + '-eps" required></p></form>') : '')
						+ '</td>'
						+ '<td id="review-'+subject.subject_id+'"><form method="post" action="'+t.attr('rel')+'" class="subject-edit-content">'
						+ '<p><label for="' + id + '-interest_rate"><?php _e('评价'); ?></label>'
						+ '<input class="text-s w-100" type="text" name="interest_rate" id="' + id + '-interest_rate" required></p>'
						+ '<p><label for="' + id + '-tags"><?php _e('标签'); ?></label>'
						+ '<input class="text-s w-100" type="text" name="tags" id="' + id + '-tags"></p>'
						+ '<p><label for="' + id + '-comment"><?php _e('内容'); ?></label>'
						+ '<textarea name="comment" id="' + id + '-comment" rows="6" class="w-100 mono"></textarea></p>'
						+ '<p><button type="submit" class="btn-s primary"><?php _e('提交'); ?></button> '
						+ '<button type="button" class="btn-s cancel"><?php _e('取消'); ?></button></p></form></td></tr>')
						.data('id', id).data('subject', subject).insertAfter(tr);

		$('input[name=ep_status]', edit).val(subject.ep_status);
		$('input[name=eps]', edit).val(subject.eps);
		$('input[name=interest_rate]', edit).val(subject.interest_rate);
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
					if(data.collection == 'collect')
						$('.subject-ep-plus', oldTr).parents('div.hidden-by-mouse').remove();
					else
						$('.subject-ep-plus', oldTr).html('ep.'+(parseInt(subject.ep_status)+1)+'已看过');
					$('.bangumi-progress', oldTr).html('<div class="bangumi-progress-inner" style="color:white; width:'+(subject.eps != '0' ? subject.ep_status/subject.eps*100 : 100)+'%"><small>'+subject.ep_status+' / '+(subject.eps != '0' ? subject.eps : '??')+'</small></div>');
					var html='';
					for(var i=0; i<subject.interest_rate; i++)
						html += '<span class="interest_rate-star interest_rate-star-rating"></span>';
					for(; i<10; i++)
						html += '<span class="interest_rate-star interest_rate-star-blank"></span>';
					$('.subject-interest_rate', oldTr).html('评价：'+ '<span class="interest_rate-star interest_rate-star-rating"></span>'.repeat(subject.interest_rate)+'<span class="interest_rate-star interest_rate-star-blank"></span>'.repeat(10-subject.interest_rate));
					$('.subject-tags', oldTr).html('标签：'+(subject.tags ? subject.tags : '<i>无</i>'));
					$('.subject-comment', oldTr).html('吐槽：'+(subject.comment ? subject.comment : '<i>无</i>'));
					$('.subject-meta', oldTr).effect('highlight');
					$('.subject-review', oldTr).effect('highlight');
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