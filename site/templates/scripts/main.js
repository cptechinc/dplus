// Well hello there. Looks like we don't have any Javascript.
// Maybe you could help a friend out and put some in here?
// Or at least, when ready, this might be a good place for it.

var nav = '#yt-menu';

$(function() {
	$('[data-toggle="tooltip"]').tooltip();
	init_datepicker();

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
});

function toggle_nav() {
	$(nav).toggle();
	$(nav).find('input[name=q]').focus();
}

function init_datepicker() {
	$('.datepicker').each(function(index) {
		$(this).datepicker({
			date: $(this).find('.date-input').val(),
			allowPastDates: true,
		});
	});
}

$.fn.extend({
	loadin: function(href, callback) {
		var parent = $(this);
		parent.html('<div></div>');

		var element = parent.find('div');
		console.log('loading ' + href + " into " +  parent.returnelementdescription());
		element.load(href, function() {
			init_datepicker();
			// init_timepicker();
			callback();
		});
	},
	returnelementdescription: function() {
		var element = $(this);
		var tag = element[0].tagName.toLowerCase();
		var classes = '';
		var id = '';
		if (element.attr('class')) {
			classes = element.attr('class').replace(' ', '.');
		}
		if (element.attr('id')) {
			id = element.attr('id');
		}
		var string = tag;
		if (classes) {
			if (classes.length) {
				string += '.'+classes;
			}
		}
		if (id) {
			if (id.length) {
				string += '#'+id;
			}
		}
		return string;
	}
});
