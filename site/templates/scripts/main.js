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

	$(".toggle-menu").on("click", function(e){
		e.preventDefault();
		e.stopPropagation();
		var offcanvas_id =  $(this).attr('data-target');
		$(offcanvas_id).toggleClass("show");
		$('body').toggleClass("offcanvas-active");
		$(".screen-overlay").toggleClass("show");
		if ($(offcanvas_id).hasClass("show")) {
			$(offcanvas_id).find('input[name=q]').focus();
		}
	});

	$(".close, .screen-overlay").click(function(e){
		$(".screen-overlay").removeClass("show");
		$(".offcanvas").removeClass("show");
		$("body").removeClass("offcanvas-active");
	});

	$(document).keyup(function(e) {
		if (e.keyCode == 27) { // escape key maps to keycode `27`
			if ($("#my_offcanvas1").hasClass("show")) {
				$("#my_offcanvas1 .close").click();
			}
		}
	});

	$('.placard').on('accepted.fu.placard', function () {
		var placard = $(this);
		var form = placard.closest('form');
		form.submit();
	});

	$("body").on('keypress', 'form.allow-enterkey-submit input', function(e) {
		if ($(this).closest('form').hasClass('allow-enterkey-submit')) {
			return true;
		} else {
			return e.which !== 13;
		}
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
		var empty_fields = $(this).find(':input:not(button)').filter(function () {
			return $(this).val() === '';
		});
		empty_fields.prop('disabled', true);
		return true;
	});

	$('input.qty-input').on('focus', function () {
		var input = $(this);
		input.attr('data-value', input.val());
		input.val('');
	});

	$('input.qty-input').on('focusout', function () {
		var input = $(this);
		var attr = input.attr('data-value');

		if (input.val().length == 0) {
			// For some browsers, `attr` is undefined; for others, `attr` is false. Check for both.
			if (typeof attr !== typeof undefined && attr !== false) {
				input.val(attr);
			}
		}
	});

	$('.create-action').on('click', function (e) {
		e.preventDefault();
		var button = $(this);
		var uri = new URI(button.attr('href'));
		swal2.fire({
			title: 'Select Action Type',
			input: 'select',
			icon: 'question',
			confirmButtonClass: 'btn btn-sm btn-success',
			cancelButtonClass: 'btn btn-sm btn-danger',
			inputClass: 'form-control',
			inputOptions: {
				task: 'Task',
				note: 'Note',
				action: 'Action',
			},
			inputPlaceholder: 'Select an Action Type',
			showCancelButton: true,
			inputValidator: (value) => {
				return new Promise((resolve) => {
					if (value.length) {
						resolve();
					} else {
						resolve('You need to select an Action Type')
					}
				})
			}
		}).then(function (result) {
			uri.addQuery('type', result.value);
			window.location.href = uri.toString();
		});
	});

	$.notifyDefaults({
		type: 'success',
		allow_dismiss: true,
		template:
			'<div data-notify="container" class="col-xs-11 col-sm-3" role="alert">' +
				'<div class="card">' +
					'<div class="container">' +
						'<button type="button" aria-hidden="true" class="close" data-notify="dismiss">×</button>' +
						'<div class="row">' +
							'<div class="col-2 d-flex justify-content-center align-items-center bg-{0} text-white">' +
								'<span data-notify="icon"></span>' +
							'</div>' +
							'<div class="col-10 alert-{0} pt-1 pb-1">' +
								'<h5 data-notify="title">{1}</h5>' +
								'<div class="notify-message">{2}</div>' +
								'<div class="progress" data-notify="progressbar">' +
									'<div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>' +
								'</div>' +
								'<a href="{3}" target="{4}" data-notify="url"></a>' +
							'</div>' +
						'</div>' +
					'</div>' +
				'</div>' +
			'</div>'
	});

	$('.phone-input').keyup(function() {
		$(this).val(format_phone($(this).val()));
		$(this).attr('minlength', '12');
	});

	$('button.delete_button').click(function(e) {
		e.preventDefault();
		var button = $(this);
		var action = $('.modal-form').attr("action");

		swal2.fire({
			title: "Confirm Deletion",
			text: "Are you sure you want to delete?",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: 'Yes',
			cancelButtonText: 'No',
			focusCancel: true,
		}).then((confirm) => {
			if (confirm.value) {
				button.closest('form').submit();
				window.location.href = action;
			}
		});
	});

	$('#loading-modal').on('show.bs.modal', function (event) {
		var modal = $(this);
		modal.find('[role=status]').addClass('spinner-border');
	});

	$('#loading-modal').on('hide.bs.modal', function (event) {
		var modal = $(this);
		modal.find('[role=status]').removeClass('spinner-border');
	});
});

$.fn.extend({
	loadin: function(href, callback) {
		var parent = $(this);
		parent.html('<div></div>');

		var element = parent.find('div');
		console.log('loading ' + href + " into " +  parent.returnElementDescription());
		element.load(href, function() {
			init_datepicker();
			callback();
		});
	},
	returnElementDescription: function() {
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
	hasParent: function(selector) {
		var element = $(this);
		return $(this).closest(selector).length > 0;
	},
	formIsCompleted: function() {
		var form = $(this);
		form.find('.required').each(function() {
			if ($(this).val() === '') {
				return false;
			}
		});
		return true;
	},
	formDisableFields: function() {

	},
	resizeModal: function(size) {
		if ($(this).hasClass('modal')){
			var modal_dialog = $(this).find('.modal-dialog');
			var modal_size = '';

			var regex_modal = /(modal-)/;
			var regex_modal_size = /(modal-)(sm|md|lg|xl)/;
			var regex_sizes = /(sm|md|lg|xl)/;

			if (regex_modal_size.test(size)) {
				modal_size = size;
			} else {
				if (regex_sizes.test(size)) {

				} else {
					size = 'md';
				}
				modal_size = 'modal-' + size;
			}

			if (regex_modal_size.test(modal_dialog.attr('class'))) {
				var attrclass = modal_dialog.attr('class');
				attrclass = attrclass.replace(regex_modal_size, modal_size);
				modal_dialog.attr('class', attrclass);
			} else {
				modal_dialog.addClass(modal_size);
			}
		}
	},
	clearValidation: function() {
		var v = $(this).validate();
		$('[name]',this).each(function(){
			v.successList.push(this);
			v.showErrors();
		});
		v.resetForm();
		v.reset();
	},
	formValues: function() {
		var form = $(this);
		if (form[0].tagName != 'FORM') {
			return false;
		}
		var values = form.serializeArray().reduce(function(obj, item) {
			obj[item.name] = item.value;
			return obj;
		}, {});
		return values;
	}
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

$('a.delete_button').click(function(e){
	e.preventDefault();
	var link = $(this);

	swal2.fire({
		title: "Confirm Deletion",
		text: "Are you sure you want to delete?",
		icon: 'warning',
		showCancelButton: true,
		confirmButtonClass: 'btn btn-success',
		cancelButtonClass: 'btn btn-danger',
		buttonsStyling: false,
		confirmButtonText: 'Yes'
	}).then((result) => {
		if (result.value) {
			window.location.href = link.attr('href');
		}
	});
});

function swal_delete_notes(callback) {
	swal2.fire({
		title: 'Confirm Deletion',
		text: 'Are you sure you want to delete?',
		icon: 'warning',
		showCancelButton: true,
		confirmButtonText: 'Yes',
		cancelButtonText: 'No',
		focusCancel: true,
	}).then((result) => {
		if (result.value) {
			callback(true);
		} else {
			callback(false);
		}
	});
}

/*==============================================================
	STRING FUNCTIONS
=============================================================*/
function validate_email(email) {
	var emailregex = /^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/;
	return emailregex.test(email);
}

function format_phone(input) {
	// Strip all characters from the input except digits
	input = input.replace(/\D/g,'');

	// Trim the remaining input to ten characters, to preserve phone number format
	input = input.substring(0,10);

	// Based upon the length of the string, we add formatting as necessary
	var size = input.length;
	if (size == 0){
		input = input;
	} else if(size < 4){
		input = input;
	} else if(size < 7){
		input = input.substring(0,3)+'-'+input.substring(3,6);
	} else {
		input = input.substring(0,3)+'-'+input.substring(3,6)+'-'+input.substring(6,10);
	}
	return input;
}

/*==============================================================
	JS Prototype FUNCTIONS
=============================================================*/
function floatParse(num) {
	if (typeof num != 'number' && num.indexOf(',')) {
		num = num.replace(",", '');
	}
	return parseFloat(num);
}

function intParse(num) {
	if (typeof num != 'number' && num.indexOf(',')) {
		num = num.replace(",", '');
	}
	return parseInt(num);
}

Number.prototype.formatMoney = function(c, d, t) {
	var n = this,
		c = isNaN(c = Math.abs(c)) ? 2 : c,
		d = d == undefined ? "." : d,
		t = t == undefined ? "," : t,
		s = n < 0 ? "-" : "",
		i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))),
		j = (j = i.length) > 3 ? j % 3 : 0;
		return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
};

Number.prototype.truncate = function(precision) {
	var number = this;
	var array = number.toString().split(".");
	array.push(array.pop().substring(0, precision));
	var trimmed =  array.join(".");
	return trimmed;
}

String.prototype.capitalize = function() {
	return this.charAt(0).toUpperCase() + this.slice(1)
}

String.prototype.urlencode = function() {
	var string = this;
	string = string.replace('!', '%21')
	return encodeURIComponent(string);
}

Array.prototype.contains = function ( needle ) {
	for (i in this) {
		if (this[i] == needle) return true;
	}
	return false;
}

// CREATE DEFAULT SWEET ALERT
const swal2 = Swal.mixin({
	customClass: {
		confirmButton: 'btn btn-success mr-3',
		cancelButton: 'btn btn-danger',
		inputClass: 'form-control',
		selectClass: 'form-control',
	},
	buttonsStyling: false,
	cancelButtonText: 'No',
	confirmButtonText: 'Yes',
	focusConfirm: false,
	focusCancel: true,
})
