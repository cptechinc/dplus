$(function() {
	var uri = URI();
	var queryData = uri.query(true);

	$('#edit-code-modal').on('hidden.bs.modal', function (event) {
		var modal = $(this);
		var button = $(event.relatedTarget); // Button that triggered the modal
		var code = modal.find('input[name=code]').val().toString();

		$('#code-table-alert').hide();
		$('.bg-success').removeClass('bg-success text-white');
		$('.highlight').removeClass('highlight');
		$('.code[data-code="'+code+'"]').addClass('highlight');
	});

	if (queryData.hasOwnProperty('focus') && queryData.focus != 'new') {
		$('.code[data-code="' + queryData.focus + '"]').addClass('highlight');
		$('html, body').animate({scrollTop: $('.code[data-code="' + queryData.focus + '"]').offset().top,},700,'linear');
	}
});
