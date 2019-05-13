// Well hello there. Looks like we don't have any Javascript.
// Maybe you could help a friend out and put some in here?
// Or at least, when ready, this might be a good place for it.

var nav = '#yt-menu';

$(function() {
	$(window).scroll(function() {
		if ($(this).scrollTop() > 50) {
			$('#back-to-top').fadeIn();
		} else {
			$('#back-to-top').fadeOut();
		}
	});

	// scroll body to 0px on click
	$('#back-to-top').click(function () {
		$('#back-to-top').tooltip('hide');
		$('body,html').animate({ scrollTop: 0 }, 800);
		return false;
	});

	$("body").on('show', '#yt-menu', function() {
		alert('');
	});

	$("body").on('keypress', 'form:not(.allow-enterkey-submit) input', function(e) {
		if (e.which === 13) {
			e.preventDefault();
			var input = $(this);

			if (input.closest('form').attr('tab-inputs') == "true") {
				var $canfocus = $('input:not([type=hidden])');
				var index = $canfocus.index(this) + 1;
				if (index >= $canfocus.length) index = 0;
				$canfocus.eq(index).focus();
			}
		}
	});

	$('form[submit-empty="false"]').submit(function () {
		var $empty_fields = $(this).find(':input').filter(function () {
			return $(this).val() === '';
		});
		$empty_fields.prop('disabled', true);
		return true;
	});
});

function toggle_nav() {
	$(nav).toggle();
	$(nav).find('input[name=q]').focus();
}

$(function() {
	$('[data-toggle="tooltip"]').tooltip();
	init_datepicker();
});

function init_datepicker() {
	$('.datepicker').each(function(index) {
		$(this).datepicker({
			date: $(this).find('.date-input').val(),
			allowPastDates: true,
		});
	});
}
