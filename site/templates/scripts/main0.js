$(function() {
	$('[data-toggle="tooltip"]').tooltip();
	init_datepicker();

	$("body").on("change", ".results-per-page-form .results-per-page", function() {
		var form = $(this).closest("form");
		var ajax = form.hasClass('ajax-load');
		var showonpage = form.find('.results-per-page').val();
		var displaypage = form.attr('action');
		var href = URI(displaypage).addQuery('display', showonpage).toString();

		if (ajax) {
			var loadinto = form.data('loadinto');
			var focuson = form.data('focus');
			loadin(href, loadinto, function() {
				if (focuson.length > 0) { $('html, body').animate({scrollTop: $(focuson).offset().top - 60}, 1000);}
			});
		} else {
			window.location.href = href;
		}
	});

	$("body").on("click", ".cart-item-search", function(e) {
		e.preventDefault();
		var button = $(this);
		var modal = config.modals.ajax;
		var loadinto = modal+" .modal-content";
		var href = URI(button.attr('href')).addQuery('modal', 'modal').normalizeQuery().toString();

		$(loadinto).loadin(href, function() {
			$(modal).modal('show');
			setTimeout(function (){ $(modal).find('.query').focus();}, 500);
		});
	});

	$("body").on("submit", "#item-search", function(e) {
		e.preventDefault();
		var form = $(this);
		var modal = config.modals.ajax;
		var loadinto = modal+" .modal-content";
		var href = URI(form.attr('action')).query(form.serialize()).addQuery('modal', 'modal').normalizeQuery().toString();

		$(loadinto).loadin(href, function() {
			$(modal).modal('show');
		});
	});
	
	$("body").on("click", ".load-link", function(e) {
		e.preventDefault();
		var button = $(this);
		var loadinto = $(this).data('loadinto');
		var focuson = $(this).data('focus');
		var href = $(this).attr('href');
		
		$(loadinto).loadin(href, function() {
			if (focuson.length > 0) {
				$('html, body').animate({scrollTop: $(focuson).offset().top - 60}, 1000);
			}
		});
	});
});

$.fn.extend({
	loadin: function(href, callback) {
		var element = $(this);
		var parent = element.parent();
		console.log('loading ' + element.returnelementdescription() + " from " + href);
		parent.load(href, function() {
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
	},
});

function init_datepicker() {
	$('.datepicker').each(function(index) {
		$(this).datepicker({
			date: $(this).find('.date-input').val(),
			allowPastDates: true,
		});
	});
}
