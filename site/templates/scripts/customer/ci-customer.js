$(function() {
	$('#select_shipto').on('change', function () {
		var select = $(this);
		form = select.closest('form');
		form.submit();
	});
});
