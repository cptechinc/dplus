$(function() {
	let formPtm  = PtmForm.getInstance();
	let alert    = PtmAlerts.getInstance();
	let server   = PtmRequests.getInstance();
	let dateRegexes = DateRegexes.getInstance();

	let momentJsFormats = {
		'mmdd': 'MMDD',
		'mm/dd': 'MM/DD',
		'm/dd': 'M/DD',
		'mmddyyyy': 'MMDDYYYY',
		'mmddyy': 'MMDDYY',
		'mm/dd/yyyy': 'MM/DD/YYYY',
		'timestamp': 'X'
	}

	if (formPtm.inputs.fields.code.val() == '') {
		formPtm.inputs.fields.code.focus();
	} else {
		formPtm.inputs.fields.description.focus();
	}

/* =============================================================
	Unsaved Fields Alert
============================================================= */
	origForm = formPtm.form.serialize();

	$("body").on("click", "a:not(#code-form .crud-submit, #ajax-modal a, .swal2-modal a, .bootstrap-select a)", function(e) {
		if (formPtm.form.serialize() !== origForm) {
			e.preventDefault();
			let a = $(this);
			let href = a.attr('href');

			alert.unsavedChanges(function(save) {
				if (save) {
					let validator = formPtm.form.validate();
					if (validator.valid()) {
						formPtm.form.find('button[type=submit]').click();
					}
				} else {
					let uri = URI();
					uri.setQuery('code', '');

					$.get(uri.toString(), function() {
						window.location.href = href;
					});
				}
			});
		}
	});

/* =============================================================
	Events
============================================================= */
	$("body").on("focusin", "#code-form input:not(input[name=code])", function(e) {
		if (formPtm.inputs.fields.code.val() == '') {
			formPtm.inputs.fields.code.focus();
		}
	});

	$("body").on("change", "#code-form input[name=code]", function(e) {
		let input = $(this);

		if (input.val() == '') {
			return false;
		}

		server.validateCode(input.val(), function(exists) {
			if (exists === true) {
				alert.codeExists(input.val(), function(editCode) {
					if (editCode) {
						let uri = URI();
						uri.setQuery('code', input.val());
						window.location.href = uri.toString();
					} else {
						location.reload();
					}
				});
			}
		});
	});

	$("body").on("change", "#code-form select[name=method]", function(e) {
		let input = $(this);

		$('.type-splits').removeClass('show');
		
		if (input.val() == codetable.config.methods.std.value){
			$('#std-splits').addClass('show');
			return true;
		}

		if (input.val() == codetable.config.methods.eom.value) {
			$('#eom-splits').addClass('show');
			return true;
		}
	});

	$("body").on("change", "#code-form input[name=expiredate]", function(e) {
		let input = $(this);

		if (input.val().length < 10 || isNaN(input.val())) {
			return true;
		}

		if (dateRegexes.regexes['mmddyyyy'].test(input.val()) === false && dateRegexes.regexes['mmddyy'].test(input.val()) === false) {
			return true;
		}

		let momentParseFormat = momentJsFormats['mmddyyyy'];

		if (dateRegexes.regexes['mmddyy'].test(input.val())) {
			momentParseFormat = momentJsFormats['mmddyy'];
		}

		let date = moment(input.val(), momentParseFormat);
		if (date.isValid() === false) {
			return false;
		}
		input.val(date.format(momentJsFormats['mm/dd/yyyy']));
	});

/* =============================================================
	Method STD Events
============================================================= */
	$("body").on("change", ".std_order_percent", function(e) {
		if (formPtm.isMethodStd() === false) {
			return false;
		}
		let input  = $(this);
		let percent = formPtm.floatVal(input.val());
		input.val(percent.toFixed(formPtm.config.fields.std_order_percent.precision));
		
		let totalPercent = formPtm.sumUpStdOrderPercents();

		if (totalPercent > 100) {
			alert.orderPercentIsOver100(function() {
				input.val(input.attr('data-lastvalue'));
				// input.closest('form').validate().element('#'+input.attr('id'));
				input.focus();
				return true;
			});
			return true;
		}

		if (input.val() == 0) {
			input.val('');
		}
	});

/* =============================================================
	Form Validation
============================================================= */
	function validateExpiredate() {
		let input = formPtm.inputs.fields.expiredate;
		let expiredate = moment(input.val(), momentJsFormats['mm/dd/yyyy']);
		if (input.val().length < 8) {
			return true;
		}
		if (expiredate.isValid() == false) {
			return false;
		}
		let minDate    = moment();
		return parseInt(expiredate.format(momentJsFormats['timestamp'])) > parseInt(minDate.format(momentJsFormats['timestamp']));
	}

	function validatestdOrderPercentTotal() {
		return formPtm.sumUpStdOrderPercents() == 100;
	}

	jQuery.validator.addMethod("expiredate", function(value, element) {
		return this.optional(element) || validateExpiredate();
	}, "Date must be a valid, future date MM/DD/YYYY");

	jQuery.validator.addMethod("stdOrderPercentTotal", function(value, element) {
		var percentTotal = formPtm.sumUpStdOrderPercents();
		var isFocused = element == document.activeElement;
		return this.optional(element) || value == 0 || (isFocused && percentTotal <= 100) || validatestdOrderPercentTotal();
	}, "Order Percent Must add up to 100");

	let validator = formPtm.form.validate({
		errorClass: "is-invalid",
		validClass: "",
		errorPlacement: function(error, element) {
			error.addClass('invalid-feedback');

			if (element.closest('.input-parent').length == 0 && element.closest('.input-group-parent').length == 0) {
				error.insertAfter(element);
				return true;
			}
			if (element.closest('.input-group-parent').length) {
				console.log(error);
				error.appendTo(element.closest('.input-group-parent'));
				return true;
			}
			error.appendTo(element.closest('.input-parent'));
		},
		rules: {
			code: {
				required: true,
				remote: {
					url: codetable.config.urls.api.validate,
					type: "get",
					data: {
						jqv: 'true',
						new: function() {
							return formCode.inputs.form.attr('data-code') == $('#code').val() ? 'false' : 'true';
						},
					}
				}
			},
			expiredate: {
				expiredate: true,
			},
			std_order_percent1: {
				required: true,
			},
		},
		submitHandler: function(form) {
			if (formPtm.isMethodEom()) {
				form.submit();
				return true;
			}
			if (formPtm.sumUpStdOrderPercents() != 100) {
				formPtm.form.find('#std-error').text('Order Percents Must add up to 100');
				return false;
			}
			form.submit();
		}
	});

	$('input.std_order_percent').each(function() {
		let input = $(this);

		let rules = {
			required: false,
			stdOrderPercentTotal: true,
		};

		if (input.attr('name') == 'std_order_percent1') {
			rules.required = true;
		}
		input.rules("add", rules);
	});

});