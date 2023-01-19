$(function() {
	let formCode = CodeFormBase.getInstance();
	let formTrm  = TrmForm.getInstance();
	let alert    = CodeAlertsBase.getInstance();
	let server   = TrmRequests.getInstance();

	if (formTrm.inputs.fields.code.val() == '') {
		formTrm.inputs.fields.code.focus();
	} else {
		formTrm.inputs.fields.description.focus();
	}

	let regexPatterns = {
		'mmddyyyy': '(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])((20)\d{2})',
		'mmddyy': '(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])(\d{2})',
		'mmdd': '(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])',
		'mm/dd': '(0[1-9]|1[012])\/(0[1-9]|[12][0-9]|3[01])',
	};

	let momentJsFormats = {
		'mmdd': 'MMYY',
		'mm/dd': 'MM/YY',
		'mmddyyyy': 'MMDDYYYY',
		'mmddyy': 'MMDDYY',
		'mm/dd/yyyy': 'MM/DD/YYYY',
		'timestamp': 'X'
	}

	let regexes = {
		'mmddyyyy': new RegExp(regexPatterns['mmddyyyy']),
		'mmddyyyy': new RegExp(regexPatterns['mmddyy']),
		'mmdd': new RegExp(regexPatterns['mmdd']),
		'mm/dd': new RegExp(regexPatterns['mm/dd']),
	};

/* =============================================================
	Unsaved Fields Alert
============================================================= */
	origForm = formCode.form.serialize();

	$("body").on("click", "a:not(#code-form .crud-submit, #ajax-modal a, .swal2-modal a, .bootstrap-select a)", function(e) {
		if (formCode.form.serialize() !== origForm) {
			e.preventDefault();
			let a = $(this);
			let href = a.attr('href');

			alert.unsavedChanges(function(save) {
				if (save) {
					let validator = formCode.form.validate();
					if (validator.valid()) {
						formCode.form.find('button[type=submit]').click();
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
		if (formCode.inputs.fields.code.val() == '') {
			formCode.inputs.fields.code.focus();
		}
	});

	$("body").on("focusin", "#code-form .eom-split input", function(e) {
		let input = $(this);

		let form       = input.closest('form');
		let validator  = form.validate();
		let formEom    = form.find('#eom-splits');
		let firstInput = formEom.find('input[name=eom_thru_day1]');

		if (input.attr('tabindex') <= firstInput.attr('tabindex')) {
			return true;
		}

		let start = parseInt(firstInput.attr('tabindex'));
		
		// Only loop up to $(this) input
		for (let i = start; i < parseInt(input.attr('tabindex')); i++) {
			let otherInput = formEom.find('input[tabindex='+i+']');

			if (otherInput.length == 0) {
				continue;
			}

			if (validator.element('#' + otherInput.attr('id')) === false) {
				otherInput.focus();
				return true;
			}
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
		
		$('.type-input').removeClass('show');
		$('.type-splits').removeClass('show');

		if (input.val() == codetable.config.methods.std.value){
			$('[name="' + codetable.config.methods.std.typeInputName + '"]').addClass('show');
			$('#std-splits').addClass('show');
			return true;
		}

		if (input.val() == codetable.config.methods.eom.value) {
			let inputTypeS = formTrm.form.find('[name=typeS]');
			inputTypeS.val(formTrm.config.fields.type.default);
			inputTypeS.change();
			$('[name="' + codetable.config.methods.eom.typeInputName + '"]').addClass('show');
			$('#eom-splits').addClass('show');
			return true;
		}
	});

	$("body").on("change", "#code-form select[name=method]", function(e) {
		let input = $(this);
		
		$('.type-input').removeClass('show');
		$('.type-splits').removeClass('show');

		if (input.val() == codetable.config.methods.std.value){
			$('[name="' + codetable.config.methods.std.typeInputName + '"]').addClass('show');
			formTrm.setReadonly(formTrm.inputs.fields.freightallow, false);
			formTrm.enableTabindex(formTrm.inputs.fields.freightallow);
			formTrm.setDisabled(formTrm.inputs.fields.freightallow, false);
			$('#std-splits').addClass('show');
			return true;
		}

		if (input.val() == codetable.config.methods.eom.value){
			$('[name="' + codetable.config.methods.eom.typeInputName + '"]').addClass('show');
			formTrm.inputs.fields.freightallow.val('N');
			formTrm.disableTabindex(formTrm.inputs.fields.freightallow);
			formTrm.setReadonly(formTrm.inputs.fields.freightallow, true);
			formTrm.setDisabled(formTrm.inputs.fields.freightallow, true);
			$('#eom-splits').addClass('show');
			return true;
		}
	});

	$("body").on("change", "#code-form input[name=ccprefix]", function(e) {
		let input = $(this);
		let parent = input.closest('.input-parent');
		let descriptionField = parent.find('.description');

		descriptionField.text('');

		if (input.val() == '') {
			return true;
		}

		server.getCreditCardCode(input.val(), function(crcdCode) {
			if (crcdCode) {
				descriptionField.text(crcdCode.description);
			}
		});
	});

	$("body").on("change", "#code-form select[name=typeS]", function(e) {
		let input = $(this);
		let ccprefixParent = formCode.inputs.fields.ccprefix.closest('.input-parent');
		
		if (input.val() != codetable.config.types.creditcard.value) {
			formCode.inputs.fields.ccprefix.val('');
			formCode.inputs.fields.ccprefix.change();
			formTrm.setReadonly(formCode.inputs.fields.ccprefix, true);
			formTrm.disableTabindex(formCode.inputs.fields.ccprefix);
			ccprefixParent.find('button[data-toggle]').attr('disabled', 'disabled');
			return true;
		}
		formTrm.setReadonly(formCode.inputs.fields.ccprefix, false);
		formTrm.enableTabindex(formCode.inputs.fields.ccprefix);
		ccprefixParent.find('button[data-toggle]').removeAttr('disabled');
	});

	$("body").on("change", "#code-form input[name=country]", function(e) {
		let input = $(this);
		let parent = input.closest('.input-parent');
		let descriptionField = parent.find('.description');

		descriptionField.text('');

		if (input.val() == '') {
			return true;
		}

		server.getCountryCode(input.val(), function(country) {
			if (country) {
				descriptionField.text(country.description);
			}
		});
	});

	$("body").on("change", "#code-form input[name=termsgroup]", function(e) {
		let input = $(this);
		let parent = input.closest('.input-parent');
		let descriptionField = parent.find('.description');

		descriptionField.text('');

		if (input.val() == '') {
			return true;
		}

		server.getTermsGroup(input.val(), function(termsgroup) {
			if (termsgroup) {
				descriptionField.text(termsgroup.description);
			}
		});
	});

	$("body").on("change", "#code-form input[name=expiredate]", function(e) {
		let input = $(this);
		if (input.val().length < 8) {
			return true;
		}

		if (isNaN(input.val())) {
			return true;
		}

		if (regexes['mmddyyyy'].test(input.val()) === false && regexes['mmddyy'].test(input.val()) === false){
			return true;
		}

		let momentParseFormat = momentJsFormats['mmddyyyy'];

		if (regexes['mmddyy'].test(input.val())) {
			momentParseFormat = momentJsFormats['mmddyy'];
		}

		let date = moment(input.val(), momentParseFormat);
		if (date.isValid() === false) {
			return false;
		}
		input.val(date.format(momentJsFormats['mm/dd/yyyy']))
	});

/* =============================================================
	Method STD Events
============================================================= */
	$("body").on("change", ".std_disc_days", function(e) {
		if (formTrm.isMethodStd() === false) {
			return false;
		}

		let input  = $(this);
		let days = input.val() == '' ? 0 : parseFloat(input.val());
		let parentGroup = input.closest('.std-discount');

		if (days > 0) {
			let inputs = [parentGroup.find('input.std_disc_day'), parentGroup.find('input.std_disc_date')];

			inputs.forEach(input => {
				formTrm.setReadonly(input, true);
				formTrm.disableTabindex(input);
			});
			return true;
		}
		formTrm.enableDisableStdDiscFieldsFromDiscPercent(parentGroup.find('.std_disc_percent'));
	});

	$("body").on("change", ".std_disc_day", function(e) {
		if (formTrm.isMethodStd() === false) {
			return false;
		}

		let input  = $(this);
		let day = input.val() == '' ? 0 : parseFloat(input.val());
		let parentGroup = input.closest('.std-discount');

		if (day == 0) {
			formTrm.enableDisableStdDiscFieldsFromDiscPercent(parentGroup.find('.std_disc_percent'));
		}

		let inputs = [parentGroup.find('input.std_disc_days'), parentGroup.find('input.std_disc_date')];
		inputs.forEach(input => {
			formTrm.setReadonly(input, true);
			formTrm.disableTabindex(input);
		});
	});

	$("body").on("change", ".std_disc_date", function(e) {
		if (formTrm.isMethodStd() === false) {
			return false;
		}

		let input  = $(this);
		let parentGroup = input.closest('.std-discount');

		if (input.val() == '') {
			formTrm.enableDisableStdDiscFieldsFromDiscPercent(parentGroup.find('.std_disc_percent'));
		}

		if (regexes['mmdd'].test(input.val())) {
			let date = moment(input.val(), momentJsFormats['mmdd']);
			input.val(date.format(momentJsFormats['mm/dd']));
		}
		
		if (regexes['mm/dd'].test(input.val()) === false) {
			return false;
		}

		let inputs = [parentGroup.find('input.std_disc_days'), parentGroup.find('input.std_disc_day')];
		inputs.forEach(input => {
			formTrm.setReadonly(input, true);
			formTrm.disableTabindex(input);
		});
	});

/* =============================================================
	Method EOM Events
============================================================= */
	$("body").on("change", ".eom_disc_percent", function(e) {
		if (formTrm.isMethodEom() === false) {
			return false;
		}

		let input  = $(this);
		let percent = input.val() == '' ? 0 : parseFloat(input.val());
		
		if (percent == 0) {
			formTrm.enableDisableEomDiscDayMonthFromPercent(input);
			return true;
		}
		input.val(percent.toFixed(formCode.config.fields.eom_disc_percent.precision));
		formTrm.enableDisableEomDiscDayMonthFromPercent(input);
	});

	$("body").on("keyup", ".eom_disc_percent", function(e) {
		if (formTrm.isMethodEom() === false) {
			return false;
		}

		let input  = $(this);
		let percent = input.val() == '' ? 0 : parseFloat(input.val());
		
		if (percent == 0) {
			formTrm.enableDisableEomDiscDayMonthFromPercent(input);
			return true;
		}		
		formTrm.enableDisableEomDiscDayMonthFromPercent(input);
	});

	$("body").on("change", ".eom_thru_day", function(e) {
		if (formTrm.isMethodEom() === false) {
			return false;
		}

		let input  = $(this);
		
		if (input.val() == '') {
			return true;
		}

		formTrm.updateEomThruDayInput(input);
		formTrm.enableDisableNextEomSplit(input);
		formTrm.setupNextEomSplit(input);
	});

/* =============================================================
	Form Validation
============================================================= */
	function validateExpiredate() {
		let input = formTrm.inputs.fields.expiredate;
		let expiredate = moment(input.val(), momentJsFormats['mm/dd/yyyy']);
		if (expiredate.isValid() == false) {
			return false;
		}
		let minDate    = moment();
		return parseInt(expiredate.format(momentJsFormats['timestamp'])) > parseInt(minDate.format(momentJsFormats['timestamp']));
	}

	function validateDateMMYYSlash(value) {
		return regexes['mm/dd'].test(value);
	}

	jQuery.validator.addMethod("expiredate", function(value, element) {
		return this.optional(element) || validateExpiredate();
	}, "Date must be a valid, future date MM/DD/YYYY");

	jQuery.validator.addMethod("dateMMYYSlash", function(value, element) {
		return this.optional(element) || validateDateMMYYSlash(value);
	}, "Date must be a valid, date MM/YY");

	let validator = formCode.form.validate({
		errorClass: "is-invalid",
		validClass: "",
		errorPlacement: function(error, element) {
			error.addClass('invalid-feedback');

			if (element.closest('.input-parent').length == 0) {
				error.insertAfter(element);
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
			termsgroup: {
				required: false,
				remote: {
					url: config.ajax.urls.json + 'mar/validate/trmg/code/',
					type: "get",
					data: {
						jqv: 'true',
						code: function() {
							return formCode.inputs.fields.termsgroup.val();
						}
					}
				}
			},
			eom_due_day1: {
				required: true,
			},
			order_percent1: {
				required: true,
			},
		},
		submitHandler: function(form) {
			form.submit();
		}
	});

	$('.eom_due_day').each(function() {
		let input = $(this);
		let parentEomSplit = input.closest('.eom-split');

		input.rules( "add", {
			required: function() {
				if (formCode.inputs.fields.method.val() != codetable.config.methods.eom.value) {
					return false;
				}
				return parentEomSplit.find('.eom_thru_day').val() != '';
			},
		});
	});

	$('.eom_thru_day').each(function() {
		let input = $(this);
		let parent = input.closest('.eom-day-range');

		input.rules( "add", {
			min: function() {
				if (formCode.inputs.fields.method.val() != codetable.config.methods.eom.value) {
					return 0;
				}
				return parseInt(parent.find('.eom_from_day').val()) + 1;
			}
		});
	});

	$('.std_disc_date').each(function() {
		let input = $(this);

		input.rules("add", {
			required: false,
			dateMMYYSlash: true,
		});
	});
});