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
		input.val(input.val().trim());

		let date = new DateFormatter(input.val(), 'mm/dd/yyyy');
		date.updateCentury();

		if (date.isValid()) {
			input.val(date.format('mm/dd/yyyy'));
		}
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
				input.closest('form').validate().element('#'+input.attr('id'));
				input.focus();
				return true;
			});
			return true;
		}

		if (input.val() == 0) {
			input.val('');
			formPtm.handleStdOrderPercentClear(input);
		}
		input.attr('data-lastvalue', percent);
		formPtm.setupNextStdSplit(input);
		formPtm.enableDisableNextStdSplit(input.closest('.std-split').data('index'));
	});

	$("body").on("keyup", ".std_disc_percent", function(e) {
		if (formPtm.isMethodStd() === false) {
			return false;
		}
		formPtm.enableDisableStdDiscFieldsFromDiscPercent($(this));
	});

	$("body").on("change", ".std_disc_percent", function(e) {
		if (formPtm.isMethodStd() === false) {
			return false;
		}

		let input  = $(this);
		let percent = formPtm.floatVal(input.val());
		input.val(percent.toFixed(formPtm.config.fields.std_disc_percent.precision));
		formPtm.enableDisableStdDiscFieldsFromDiscPercent(input);
		
		if (percent > 0) {
			return true;
		}
		input.val('');
		let inputs = formPtm.getStdDiscFieldsByStdDiscGroup(input.closest('.std-discount'));
		Object.values(inputs).forEach(sinput => {
			sinput.val('');
		});
	});
	
	$("body").on("keyup", ".std_disc_days", function(e) {
		if (formPtm.isMethodStd() === false || $(this).attr('readonly') !== undefined) {
			return false;
		}
		formPtm.enableDisableStdDiscFieldsFromDays($(this));
	});

	$("body").on("change", ".std_disc_days", function(e) {
		if (formPtm.isMethodStd() === false) {
			return false;
		}

		let input  = $(this);
		if (input.val() == 0) {
			input.val('');
		}
		formPtm.enableDisableStdDiscFieldsFromDays(input);
	});

	$("body").on("keyup", ".std_disc_day", function(e) {
		if (formPtm.isMethodStd() === false || $(this).attr('readonly') !== undefined) {
			return false;
		}
		formPtm.enableDisableStdDiscFieldsFromDay($(this));
	});

	$("body").on("change", ".std_disc_day", function(e) {
		if (formPtm.isMethodStd() === false) {
			return false;
		}

		let input  = $(this);
		if (input.val() == 0) {
			input.val('');
		}
		formPtm.enableDisableStdDiscFieldsFromDay(input);
	});

	$("body").on("keyup", ".std_disc_date", function(e) {
		if (formPtm.isMethodStd() === false || $(this).attr('readonly') !== undefined) {
			return false;
		}

		let input  = $(this);

		if (input.val().length > 3 && dateRegexes.regexes['mmdd'].test(input.val()) === false && dateRegexes.regexes['mm/dd'].test(input.val()) === false) {
			input.closest('form').validate().element('#' + input.attr('id'));
		}
	});

	$("body").on("change", ".std_disc_date", function(e) {
		if (formPtm.isMethodStd() === false) {
			return false;
		}

		let input  = $(this);
		let parentGroup = input.closest('.std-discount');

		input.val(input.val().trim());

		if (input.val().trim() == '') {
			formPtm.enableDisableStdDiscFieldsFromDiscPercent(parentGroup.find('.std_disc_percent'));
		}

		if (dateRegexes.regexes['mmdd'].test(input.val())) {
			let date = moment(input.val(), momentJsFormats['mmdd']);
			input.val(date.format(momentJsFormats['mm/dd']));
		}

		if (dateRegexes.regexes['m/dd'].test(input.val())) {
			let date = moment(input.val(), momentJsFormats['m/dd']);
			input.val(date.format(momentJsFormats['mm/dd']));
		}
		
		if (dateRegexes.regexes['mm/dd'].test(input.val()) === false) {
			return false;
		}
		formPtm.enableDisableStdDiscFieldsFromDate(input);
	});

	$("body").on("keyup", ".std_due_days", function(e) {
		if (formPtm.isMethodStd() === false || $(this).attr('readonly') !== undefined) {
			return false;
		}

		let input = $(this);
		input.val(input.val().trim());
		formPtm.enableDisableStdPrimaryDueFieldsFromDueDays(input);
	});

	$("body").on("change", ".std_due_days", function(e) {
		if (formPtm.isMethodStd() === false) {
			return false;
		}

		let input  = $(this);
		input.val(input.val().trim());
		let days = formPtm.floatVal(input.val());
		if (days == 0) {
			input.val('');
		}
		formPtm.enableDisableStdPrimaryDueFieldsFromDueDays(input);
	});

	$("body").on("keyup", ".std_due_day", function(e) {
		if (formPtm.isMethodStd() === false || $(this).attr('readonly') !== undefined) {
			return false;
		}

		let input  = $(this);
		formPtm.enableDisableStdDependentFieldsFromDueDay(input);
		formPtm.enableDisableStdPrimaryDueFieldsFromDueDay(input);
	});

	$("body").on("change", ".std_due_day", function(e) {
		if (formPtm.isMethodStd() === false) {
			return false;
		}

		let input  = $(this);
		let day = input.val().trim() == '' ? 0 : parseInt(input.val().trim());

		if (day == 0) {
			input.val('');
			input.closest('.std-split').find('input.std_plus_months').val('');
		}
		formPtm.enableDisableStdDependentFieldsFromDueDay(input);
		formPtm.enableDisableStdPrimaryDueFieldsFromDueDay(input);
	});

	
/* =============================================================
	Form Validation
============================================================= */
	function validatestdOrderPercentTotal() {
		return formPtm.sumUpStdOrderPercents() == 100;
	}

	jQuery.validator.addMethod("dateMMDDYYYYSlash", function(value, element) {
		return this.optional(element) || Validator.getInstance().dateMMDDYYYYSlash(value);
	}, "Date must be a valid date (MM/DD/YYYY)");

	jQuery.validator.addMethod("futuredate", function(value, element) {
		return this.optional(element) || Validator.getInstance().dateIsInFuture(value, 'mm/dd/yyyy');
	}, "Date must be in the future");


	jQuery.validator.addMethod("stdOrderPercentTotal", function(value, element) {
		var percentTotal = formPtm.sumUpStdOrderPercents();
		var isFocused = element == document.activeElement;
		return this.optional(element) || value == 0 || (isFocused && percentTotal <= 100) || validatestdOrderPercentTotal();
	}, "Order Percent Must add up to 100");

	let validator = formPtm.form.validate({
		onkeyup: false,
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
				dateMMDDYYYYSlash: true,
				futuredate: true,
				normalizer: function(value) {
					return value.trim();
				},
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