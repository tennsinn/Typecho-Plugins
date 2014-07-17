$(function(){
	$('.words-words .words-button').click(function() {
		$('#words-reply').insertAfter($(this).parent()).css('display', 'block');
		$('#words-reply-wid').val($(this).attr('wid'));
		$('#words-reply-parent').val(0)
	});

	$('.words-comments .words-button').click(function(){
		$('#words-reply').insertAfter($(this).parent()).css('display', 'block');
		$('#words-reply-wid').val($(this).attr('wid'));
		$('#words-reply-parent').val($(this).attr('cid'));
	});

	$('.words-comments-cancel').click(function(){
		$('#words-reply').css('display', 'none');
	})
})