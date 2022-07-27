$(function() {
	$("body").on("click", "#bins-modal .select-bin", function(e) {
		e.preventDefault();
		var button = $(this);
		var binID = button.data('binid');
		$('input[name=binID]').val(binID);
		button.closest('.modal').modal('hide');
	});

	$("body").on("click", ".show-hide-all", function(e) {
		e.preventDefault();
		var button = $(this);

		if (button.attr('showing') == 'true') {
			$('.collapse-lotserials').removeClass('show');
			button.attr('showing', 'false');
		} else {
			$('.collapse-lotserials').addClass('show');
			button.attr('showing', 'true');
		}
	});
});
