$(function() {
	$("body").on("click", ".show-hide-all", function(e) {
		e.preventDefault();
		var button = $(this);

		if (button.attr('showing') == 'true') {
			$('.collapse-lotserial').removeClass('show');
			button.attr('showing', 'false');
		} else {
			$('.collapse-lotserial').addClass('show');
			button.attr('showing', 'true');
		}
	});
});
