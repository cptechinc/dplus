$(function() {
	let formCode = CodeFormBase.getInstance();
	let formTrm  = TrmForm.getInstance();
	let alert    = TrmAlerts.getInstance();
	let server   = TrmRequests.getInstance();
	let dateRegexes = DateRegexes.getInstance();

	if (formTrm.inputs.fields.code.val() == '') {
		formTrm.inputs.fields.code.focus();
	} else {
		formTrm.inputs.fields.description.focus();
	}

	let momentJsFormats = {
		'mmdd': 'MMDD',
		'mm/dd': 'MM/DD',
		'm/dd': 'M/DD',
		'mmddyyyy': 'MMDDYYYY',
		'mmddyy': 'MMDDYY',
		'mm/dd/yyyy': 'MM/DD/YYYY',
		'timestamp': 'X'
	}

/* =============================================================
	Unsaved Fields Alert
============================================================= */
	origForm = formCode.form.serialize();

	$("body").on("click", "a:not(#code-form .crud-submit, #ajax-modal a, .swal2-modal a, .bootstrap-select a, #code-form .delete_button)", function(e) {
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
					if ($('.is-invalid').length) {
						$('.is-invalid').focus();
						return true;
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
				// Check negative tabindexes
				otherInput = formEom.find('input[tabindex="-'+i+'"]');

				if (otherInput.length == 0) {
					continue;
				}
			}

			if (validator.element('#' + otherInput.attr('id')) === false) {
				otherInput.focus();
				return true;
			}
		}
	});

	$("body").on("focusin", "#code-form .std-split input", function(e) {
		let input = $(this);

		let form       = input.closest('form');
		let validator  = form.validate();
		let formStd    = form.find('#std-splits');
		let firstInput = formStd.find('input[name=order_percent1]');

		if (input.attr('tabindex') <= firstInput.attr('tabindex') || input.hasClass('is-invalid')) {
			return true;
		}

		let start = parseInt(firstInput.attr('tabindex'));
		
		// Only loop up to $(this) input
		for (let i = start; i < parseInt(input.attr('tabindex')); i++) {
			let otherInput = formStd.find('input[tabindex='+i+']');

			if (otherInput.length == 0) {
				// Check negative tabindexes
				otherInput = formStd.find('input[tabindex="-'+i+'"]');

				if (otherInput.length == 0) {
					continue;
				}
			}

			if (validator.element('#' + otherInput.attr('id')) === false) {
				if (otherInput.hasClass('order_percent') && input.hasClass('order_percent')) {
					return true;
				}
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
		input.val(input.val().trim());
	});

	$("body").on("change", "#code-form input[name=ccprefix]", function(e) {
		let input = $(this);
		let parent = input.closest('.input-parent');
		let descriptionField = parent.find('.description');

		descriptionField.text('');

		if (input.val().trim() == '') {
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
		input.val(input.val().trim());
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
				return true;
			}
			input.focus();
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

		if (dateRegexes.regexes['mmddyyyy'].test(input.val()) === false && dateRegexes.regexes['mmddyy'].test(input.val()) === false){
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
		input.change();
	});

/* =============================================================
	Method STD Events
============================================================= */
	$("body").on("change", ".order_percent", function(e) {
		if (formTrm.isMethodStd() === false) {
			return false;
		}

		let input  = $(this);
		let percent = input.val() == '' ? 0 : parseFloat(input.val());
		input.val(percent.toFixed(formTrm.config.fields.order_percent.precision));
		let totalPercent = formTrm.sumUpStdOrderPercents();

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
			formTrm.shiftSplitValuesUp(input);
			let allInputs = formTrm.getAllStdInputs(true);
			if (input.closest('.std-split').data('index') > allInputs.lastindex) {
				formTrm.clearSplitInputs(input);
			}
		}
		
		formTrm.enableDisableThisStdSplitInputs(input);
		formTrm.enableDisableNextStdSplit(input);
		formTrm.setupNextStdSplit(input);
		input.attr('data-lastvalue', input.val());

		if (input.val() == '' || percent == 0) {
			formTrm.form.find('input[name=order_percent'+(input.closest('.std-split').data('index') - 1)+']').change();
			formTrm.form.find('input[name=order_percent'+(input.closest('.std-split').data('index') - 1)+']').focus();
		}

		if (formTrm.sumUpStdOrderPercents() == 100) {
			formTrm.clearStdOrderPercentErrors();
		}
	});

	$("body").on("change", "input[name=order_percent1]", function(e) {
		if (formTrm.isMethodStd() === false) {
			return false;
		}

		let input  = $(this);
		
		if (input.val() == 100) {
			formTrm.setReadonly(formTrm.inputs.fields.freightallow, false);
			formTrm.enableTabindex(formTrm.inputs.fields.freightallow);
			formTrm.setDisabled(formTrm.inputs.fields.freightallow, false);
			return true;
		}
		formTrm.inputs.fields.freightallow.val('N');
		formTrm.setReadonly(formTrm.inputs.fields.freightallow, true);
		formTrm.disableTabindex(formTrm.inputs.fields.freightallow);
		formTrm.setDisabled(formTrm.inputs.fields.freightallow, true);
	});

	$("body").on("keyup", ".std_disc_percent", function(e) {
		if (formTrm.isMethodStd() === false) {
			return false;
		}

		let input  = $(this);
		formTrm.enableDisableStdDiscFieldsFromDiscPercent(input);
	});

	$("body").on("change", ".std_disc_percent", function(e) {
		if (formTrm.isMethodStd() === false) {
			return false;
		}

		let input  = $(this);
		let percent = input.val() == '' ? 0.00 : parseFloat(input.val());
		formTrm.enableDisableStdDiscFieldsFromDiscPercent(input);
		
		if (percent > 0) {
			input.val(percent.toFixed(formTrm.config.fields.std_disc_percent.precision));
			return true;
		}
		input.val('');
		let inputs = formTrm.getStdDiscFieldsByStdDiscGroup(input.closest('.std-discount'));
		Object.values(inputs).forEach(sinput => {
			sinput.val('');
		});
	});

	$("body").on("keyup", ".std_disc_days", function(e) {
		if (formTrm.isMethodStd() === false || $(this).attr('readonly') !== undefined) {
			return false;
		}
		let input = $(this);
		input.val(input.val().trim());
		formTrm.enableDisableStdDiscFieldsFromDays($(this));
	});

	$("body").on("change", ".std_disc_days", function(e) {
		if (formTrm.isMethodStd() === false) {
			return false;
		}

		let input  = $(this);
		if (input.val() == '0') {
			input.val('');
		}
		formTrm.enableDisableStdDiscFieldsFromDays(input);
	});

	$("body").on("keyup", ".std_disc_day", function(e) {
		if (formTrm.isMethodStd() === false || $(this).attr('readonly') !== undefined) {
			return false;
		}
		let input = $(this);
		input.val(input.val().trim());
		formTrm.enableDisableStdDiscFieldsFromDay($(this));
	});

	$("body").on("change", ".std_disc_day", function(e) {
		if (formTrm.isMethodStd() === false) {
			return false;
		}

		let input  = $(this);
		if (input.val() == '0') {
			input.val('');
		}
		formTrm.enableDisableStdDiscFieldsFromDay(input);
	});

	$("body").on("keyup", ".std_disc_date", function(e) {
		if (formTrm.isMethodStd() === false || $(this).attr('readonly') !== undefined) {
			return false;
		}

		let input  = $(this);
		input.val(input.val().trim());

		if (input.val().length > 3 && dateRegexes.regexes['mmdd'].test(input.val()) === false && dateRegexes.regexes['mm/dd'].test(input.val()) === false) {
			input.closest('form').validate().element('#' + input.attr('id'));
		}
	});

	$("body").on("change", ".std_disc_date", function(e) {
		if (formTrm.isMethodStd() === false) {
			return false;
		}

		let input  = $(this);
		let parentGroup = input.closest('.std-discount');

		input.val(input.val().trim());

		if (input.val().trim() == '') {
			formTrm.enableDisableStdDiscFieldsFromDiscPercent(parentGroup.find('.std_disc_percent'));
		}

		if (dateRegexes.regexes['mmdd'].test(input.val())) {
			let date = moment(input.val(), momentJsFormats['mmdd']);
			input.val(date.format(momentJsFormats['mm/dd']));
			input.closest('form').validate().element('#'.input.attr('id'));
		}

		if (dateRegexes.regexes['m/dd'].test(input.val())) {
			let date = moment(input.val(), momentJsFormats['m/dd']);
			input.val(date.format(momentJsFormats['mm/dd']));
			input.closest('form').validate().element('#'.input.attr('id'));
		}
		
		if (dateRegexes.regexes['mm/dd'].test(input.val()) === false) {
			return false;
		}
		formTrm.enableDisableStdDiscFieldsFromDate(input);
	});

	$("body").on("keyup", ".std_due_days", function(e) {
		if (formTrm.isMethodStd() === false || $(this).attr('readonly') !== undefined) {
			return false;
		}

		let input  = $(this);
		input.val(input.val().trim());
		formTrm.enableDisableStdPrimaryDueFieldsFromDueDays(input);
	});
	
	$("body").on("change", ".std_due_days", function(e) {
		if (formTrm.isMethodStd() === false) {
			return false;
		}

		let input  = $(this);
		input.val(input.val().trim());
		let days = input.val() == '' ? 0 : parseInt(input.val().trim());
		formTrm.enableDisableStdPrimaryDueFieldsFromDueDays(input);

		if (days == 0) {
			input.val('');
		}
	});

	$("body").on("keyup", ".std_due_day", function(e) {
		if (formTrm.isMethodStd() === false || $(this).attr('readonly') !== undefined) {
			return false;
		}

		let input = $(this);
		input.val(input.val().trim());
		formTrm.enableDisableStdDependentFieldsFromDueDay(input);
		formTrm.enableDisableStdPrimaryDueFieldsFromDueDay(input);
	});

	$("body").on("change", ".std_due_day", function(e) {
		if (formTrm.isMethodStd() === false) {
			return false;
		}

		let input  = $(this);
		let day = input.val().trim() == '' ? 0 : parseInt(input.val().trim());

		if (day == 0) {
			input.val('');
			input.closest('.std-split').find('input.std_plus_months').val('');
		}
		formTrm.enableDisableStdDependentFieldsFromDueDay(input);
		formTrm.enableDisableStdPrimaryDueFieldsFromDueDay(input);
	});

	$("body").on("keyup", ".std_plus_months", function(e) {
		if (formTrm.isMethodStd() === false || $(this).attr('readonly') !== undefined) {
			return false;
		}

		let input = $(this);
		input.val(input.val().trim());
	});

	$("body").on("keyup", ".std_due_date", function(e) {
		if (formTrm.isMethodStd() === false || $(this).attr('readonly') !== undefined) {
			return false;
		}

		let input = $(this);
		input.val(input.val().trim());

		if (input.val().length > 3 && dateRegexes.regexes['mmdd'].test(input.val()) === false && dateRegexes.regexes['mm/dd'].test(input.val()) === false) {
			input.closest('form').validate().element('#' + input.attr('id'))
		}

		if (dateRegexes.regexes['mmdd'].test(input.val())) {
			let date = moment(input.val(), momentJsFormats['mmdd']);
			input.val(date.format(momentJsFormats['mm/dd']));
			input.closest('form').validate().element('#' + input.attr('id'))
		}
		formTrm.enableDisableStdDependentFieldsFromDueDate(input);
		formTrm.enableDisableStdPrimaryDueFieldsFromDueDate(input);
	});

	$("body").on("change", ".std_due_date", function(e) {
		if (formTrm.isMethodStd() === false) {
			return false;
		}

		let input  = $(this);

		if (input.val() == '') {
			formTrm.enableDisableStdDependentFieldsFromDueDate(input);
			formTrm.enableDisableStdPrimaryDueFieldsFromDueDate(input);
			input.closest('.std-split').find('input.std_plus_years').val('');
		}

		if (dateRegexes.regexes['mmdd'].test(input.val())) {
			let date = moment(input.val(), momentJsFormats['mmdd']);
			input.val(date.format(momentJsFormats['mm/dd']));
			input.closest('form').validate().element('#'.input.attr('id'));
		}

		formTrm.enableDisableStdDependentFieldsFromDueDate(input);
		formTrm.enableDisableStdPrimaryDueFieldsFromDueDate(input);
	});

	$("body").on("keyup", ".std_plus_years", function(e) {
		if (formTrm.isMethodStd() === false || $(this).attr('readonly') !== undefined) {
			return false;
		}

		let input = $(this);
		input.val(input.val().trim());
	});

/* =============================================================
	Method EOM Events
============================================================= */
	$("body").on("change", ".eom_disc_percent", function(e) {
		if (formTrm.isMethodEom() === false) {
			return false;
		}

		let input  = $(this);
		input.val(input.val().trim());
		let percent = input.val() == '' ? 0 : parseFloat(input.val());
		
		if (percent == 0) {
			formTrm.enableDisableEomDiscFieldsFromPercent(input);
			return true;
		}
		input.val(percent.toFixed(formCode.config.fields.eom_disc_percent.precision));
		formTrm.enableDisableEomDiscFieldsFromPercent(input);
	});

	$("body").on("keyup", ".eom_disc_percent", function(e) {
		if (formTrm.isMethodEom() === false || $(this).attr('readonly') !== undefined) {
			return false;
		}

		let input  = $(this);
		let percent = input.val() == '' ? 0 : parseFloat(input.val());
		if (percent == 0) {
			formTrm.enableDisableEomDiscFieldsFromPercent(input);
			return true;
		}
		formTrm.enableDisableEomDiscFieldsFromPercent(input);
	});


	$("body").on("change", ".eom_thru_day", function(e) {
		if (formTrm.isMethodEom() === false) {
			return false;
		}

		let input  = $(this);
		let parent = input.closest('.eom-split');
		
		if (input.val() == '') {
			return true;
		}

		formTrm.updateEomThruDayInput(input);
		formTrm.setupNextEomSplits(input);
		formTrm.enableDisableNextEomSplits(input);
		validator.element('#' + input.attr('id'));

		if ($('input.eom_thru_day.is-invalid').length) {
			$('input.eom_thru_day.is-invalid').focus();
		}
	});

	$("body").on("keyup", ".eom_disc_day", function(e) {
		if (formTrm.isMethodEom() === false || $(this).attr('readonly') !== undefined) {
			return false;
		}

		let input  = $(this);
		input.val(input.val().trim());
	});

	$("body").on("keyup", ".eom_disc_months", function(e) {
		if (formTrm.isMethodEom() === false || $(this).attr('readonly') !== undefined) {
			return false;
		}

		let input  = $(this);
		input.val(input.val().trim());
	});

	$("body").on("keyup", ".eom_due_day", function(e) {
		if (formTrm.isMethodEom() === false || $(this).attr('readonly') !== undefined) {
			return false;
		}

		let input  = $(this);
		input.val(input.val().trim());
	});

	$("body").on("keyup", ".eom_plus_months", function(e) {
		if (formTrm.isMethodEom() === false || $(this).attr('readonly') !== undefined) {
			return false;
		}

		let input  = $(this);
		input.val(input.val().trim());
	});

/* =============================================================
	Form Validation
============================================================= */
	function validateExpiredate() {
		let input = formTrm.inputs.fields.expiredate;
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

	function validateDateMMDDSlash(value) {
		return dateRegexes.regexes['mm/dd'].test(value);
	}

	function validatestdOrderPercentTotal() {
		return formTrm.sumUpStdOrderPercents() == 100;
	}

	function validateStdDiscountFieldGroup(element, value) {
		let parent = $(element).closest('.std-discount');

		if (parent.find('.std_disc_percent').val() == '') {
			return true;
		}
		let valid = false;
		let inputs = [parent.find('.std_disc_days'), parent.find('.std_disc_day'), parent.find('.std_disc_date')];

		inputs.forEach(input => {
			if (input.val() != '') {
				valid = true;
			}
		});
		return valid;
	}

	function validateStdDueFieldGroup(element) {
		let parent = $(element).closest('.std-due');

		if (parent.closest('.std-split').find('input.order_percent').val() == '') {
			return true;
		}

		let inputs = [parent.find('.std_due_days'), parent.find('.std_due_day'), parent.find('.std_due_date')];

		let valid = false;
		inputs.forEach(input => {
			if (input.val() != '') {
				valid = true;
			}
		});
		return valid;
	}

	function validateEomThruDay(element, value) {
		let parent = $(element).closest('.eom-day-range');
		return value >= parseInt(parent.find('.eom_from_day').val()) + 1;
	}

	jQuery.validator.addMethod("expiredate", function(value, element) {
		return this.optional(element) || validateExpiredate();
	}, "Date must be a valid, future date MM/DD/YYYY");

	jQuery.validator.addMethod("dateMMDDSlash", function(value, element) {
		var isFocused = element == document.activeElement;
		return this.optional(element) || validateDateMMDDSlash(value);
	}, "Date must be a valid, date MM/DD");

	jQuery.validator.addMethod("stdOrderPercentTotal", function(value, element) {
		var percentTotal = formTrm.sumUpStdOrderPercents();
		var isFocused = element == document.activeElement;
		return this.optional(element) || value == 0 || (isFocused && percentTotal <= 100) || validatestdOrderPercentTotal();
	}, "Order Percent Must add up to 100");

	jQuery.validator.addMethod("stdDiscFieldGroup", function(value, element) {
		return validateStdDiscountFieldGroup(element, value);
	}, "Enter Discount Days, Day, or Date");

	jQuery.validator.addMethod("stdDueFieldGroup", function(value, element) {
		return validateStdDueFieldGroup(element, value);
	}, "Enter Due Days, Day, or Date");

	jQuery.validator.addMethod("eomThruDay", function(value, element) {
		var isFocused = element == document.activeElement;
		return this.optional(element) || (isFocused) || validateEomThruDay(element, value);
	}, "Invalid Thru Day");

	let validator = formCode.form.validate({
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
				error.appendTo(element.closest('.input-group-parent'));
				return true;
			}
			error.appendTo(element.closest('.input-parent'));
		},
		rules: {
			onkeyup: false,
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
			ccprefix: {
				required: false,
				remote: {
					url: config.ajax.urls.json + 'mar/validate/crcd/code/',
					type: "get",
					data: {
						jqv: 'true',
						code: function() {
							return formCode.inputs.fields.ccprefix.val();
						}
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
			country: {
				required: false,
				remote: {
					url: config.ajax.urls.json + 'mar/validate/cocom/code/',
					type: "get",
					data: {
						jqv: 'true',
						code: function() {
							return formCode.inputs.fields.country.val();
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
			if (formTrm.isMethodEom()) {
				form.submit();
				return true;
			}
			if (formTrm.sumUpStdOrderPercents() != 100) {
				formTrm.form.find('#std-error').text('Order Perents Must add up to 100');
				return false;
			}
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
			required: function() {
				return parent.find('.eom_from_day').val() != '';
			},
			min: function() {
				if (formCode.inputs.fields.method.val() != codetable.config.methods.eom.value) {
					return 0;
				}
				return 0;
				return parseInt(parent.find('.eom_from_day').val()) + 1;
			},
			eomThruDay: true,
		});
	});

	$('.std_disc_date').each(function() {
		let input = $(this);

		input.rules("add", {
			required: false,
			dateMMDDSlash: true,
			stdDiscFieldGroup: true,
		});
	});

	$('.std_due_date').each(function() {
		let input = $(this);

		input.rules("add", {
			required: false,
			dateMMDDSlash: true,
			stdDueFieldGroup: true,
		});
	});

	$('input.order_percent').each(function() {
		let input = $(this);

		input.rules("add", {
			required: false,
			stdOrderPercentTotal: true,
		});
	});
});