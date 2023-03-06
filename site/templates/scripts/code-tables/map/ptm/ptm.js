$(function() {
	let formPtm  = PtmForm.getInstance();
	let alert    = PtmAlerts.getInstance();
	let server   = PtmRequests.getInstance();
	let dateRegexes = DateRegexes.getInstance();

	if (formPtm.inputs.fields.code.val() == '') {
		formPtm.inputs.fields.code.focus();
	} else {
		formPtm.inputs.fields.description.focus();
	}

/* =============================================================
	Unsaved Fields Alert
============================================================= */
	origForm = formPtm.form.serialize();

	$("body").on("click", "a:not(#code-form .crud-submit, #ajax-modal a, .swal2-modal a, .bootstrap-select a, #code-form a.delete_button)", function(e) {
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

	$("body").on("focusin", "#code-form .std-split input", function(e) {
		let input = $(this);

		let form       = input.closest('form');
		let validator  = form.validate();
		let formStd    = form.find('#std-splits');
		let firstInput = formStd.find('input[name=std_order_percent1]');

		if (input.attr('tabindex') <= firstInput.attr('tabindex')) {
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
				otherInput.focus();
				return true;
			}
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
		input.attr('data-lastvalue', formPtm.floatVal(input.val()));
		formPtm.setupNextStdSplit(input);
		formPtm.enableDisableNextStdSplit(input.closest('.std-split').data('index'));
	});

	$("body").on("keyup", ".std_disc_percent", function(e) {
		if (formPtm.isMethodStd() === false) {
			return false;
		}
		let input  = $(this);
		input.val(input.val().trim());
		formPtm.enableDisableStdDiscFieldsFromDiscPercent(input);
	});

	$("body").on("change", ".std_disc_percent", function(e) {
		if (formPtm.isMethodStd() === false) {
			return false;
		}

		let input  = $(this);
		input.val(input.val().trim());
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
		let input  = $(this);
		input.val(input.val().trim());
		formPtm.enableDisableStdDiscFieldsFromDays(input);
	});

	$("body").on("change", ".std_disc_days", function(e) {
		if (formPtm.isMethodStd() === false) {
			return false;
		}

		let input  = $(this);
		input.val(input.val().trim());
		if (input.val() == 0) {
			input.val('');
		}
		formPtm.enableDisableStdDiscFieldsFromDays(input);
	});

	$("body").on("keyup", ".std_disc_day", function(e) {
		if (formPtm.isMethodStd() === false || $(this).attr('readonly') !== undefined) {
			return false;
		}
		let input  = $(this);
		input.val(input.val().trim());
		formPtm.enableDisableStdDiscFieldsFromDay($(this));
	});

	$("body").on("change", ".std_disc_day", function(e) {
		if (formPtm.isMethodStd() === false) {
			return false;
		}

		let input  = $(this);
		input.val(input.val().trim());
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
		input.val(input.val().trim());
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

		let date = new DateFormatter(input.val(), 'mm/dd');

		if (date.isValid()) {
			input.val(date.format('mm/dd'));
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
		input.val(input.val().trim());
		formPtm.enableDisableStdDependentFieldsFromDueDay(input);
		formPtm.enableDisableStdPrimaryDueFieldsFromDueDay(input);
	});

	$("body").on("change", ".std_due_day", function(e) {
		if (formPtm.isMethodStd() === false) {
			return false;
		}

		let input  = $(this);
		input.val(input.val().trim());
		let day = input.val() == '' ? 0 : parseInt(input.val().trim());

		if (day == 0) {
			input.val('');
			input.closest('.std-split').find('input.std_plus_months').val('');
		}
		formPtm.enableDisableStdDependentFieldsFromDueDay(input);
		formPtm.enableDisableStdPrimaryDueFieldsFromDueDay(input);
	});

	$("body").on("keyup", ".std_due_date", function(e) {
		if (formPtm.isMethodStd() === false || $(this).attr('readonly') !== undefined) {
			return false;
		}

		let input  = $(this);
		input.val(input.val().trim());

		if (input.val().length > 3) {
			let date = new DateFormatter(input.val(), 'mm/dd');

			if (date.isValid()) {
				input.val(date.format('mm/dd'));
			}
		}
		
		formPtm.enableDisableStdDependentFieldsFromDueDate(input);
		formPtm.enableDisableStdPrimaryDueFieldsFromDueDate(input);
	});

	$("body").on("change", ".std_due_date", function(e) {
		if (formPtm.isMethodStd() === false) {
			return false;
		}

		let input  = $(this);
		input.val(input.val().trim());
		
		if (input.val() == '') {
			formPtm.enableDisableStdDependentFieldsFromDueDate(input);
			formPtm.enableDisableStdPrimaryDueFieldsFromDueDate(input);
			input.closest('.std-split').find('input.std_plus_years').val('');
			return true;
		}

		let date = new DateFormatter(input.val(), 'mm/dd');

		if (date.isValid()) {
			input.val(date.format('mm/dd'));
		}
		formPtm.enableDisableStdDependentFieldsFromDueDate(input);
		formPtm.enableDisableStdPrimaryDueFieldsFromDueDate(input);
	});

/* =============================================================
	Method EOM Events
============================================================= */
	$("body").on("change", ".eom_thru_day", function(e) {
		if (formPtm.isMethodEom() === false) {
			return false;
		}
		let input  = $(this);
		input.val(input.val().trim());

		if (input.val() == '') {
			return true;
		}

		formPtm.updateEomThruDayInput(input);
		formPtm.setupNextEomSplits(input);
		formPtm.enableDisableNextEomSplits(input);

		if ($('input.eom_thru_day.is-invalid').length) {
			$('input.eom_thru_day.is-invalid').focus();
		}
	});

	$("body").on("keyup", ".eom_disc_percent", function(e) {
		if (formPtm.isMethodEom() === false || $(this).attr('readonly') !== undefined) {
			return false;
		}

		let input  = $(this);
		input.val(input.val().trim());
		let percent = input.val() == '' ? 0 : parseFloat(input.val());
		if (percent == 0) {
			formPtm.enableDisableEomDiscFieldsFromPercent(input);
			return true;
		}
		formPtm.enableDisableEomDiscFieldsFromPercent(input);
	});

	$("body").on("change", ".eom_disc_percent", function(e) {
		if (formPtm.isMethodEom() === false) {
			return false;
		}

		let input  = $(this);
		input.val(input.val().trim());
		let percent = input.val() == '' ? 0 : parseFloat(input.val());
		
		if (percent == 0) {
			formPtm.enableDisableEomDiscFieldsFromPercent(input);
			return true;
		}
		input.val(percent.toFixed(formPtm.config.fields.eom_disc_percent.precision));
		formPtm.enableDisableEomDiscFieldsFromPercent(input);
	});

	$("body").on("keyup", ".eom_disc_day", function(e) {
		if (formPtm.isMethodEom() === false || $(this).attr('readonly') !== undefined) {
			return false;
		}

		let input  = $(this);
		input.val(input.val().trim());
	});

	$("body").on("keyup", ".eom_disc_months", function(e) {
		if (formPtm.isMethodEom() === false || $(this).attr('readonly') !== undefined) {
			return false;
		}

		let input  = $(this);
		input.val(input.val().trim());
	});

	$("body").on("keyup", ".eom_due_day", function(e) {
		if (formPtm.isMethodEom() === false || $(this).attr('readonly') !== undefined) {
			return false;
		}

		let input  = $(this);
		input.val(input.val().trim());
	});

	$("body").on("keyup", ".eom_plus_months", function(e) {
		if (formPtm.isMethodEom() === false || $(this).attr('readonly') !== undefined) {
			return false;
		}

		let input  = $(this);
		input.val(input.val().trim());
	});


/* =============================================================
	Form Validation
============================================================= */
	function validatestdOrderPercentTotal() {
		return formPtm.sumUpStdOrderPercents() == 100;
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

		if (parent.closest('.std-split').find('input.std_order_percent').val() == '') {
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

	jQuery.validator.addMethod("dateMMDDYYYYSlash", function(value, element) {
		return this.optional(element) || Validator.getInstance().dateMMDDYYYYSlash(value);
	}, "Date must be a valid date (MM/DD/YYYY)");

	jQuery.validator.addMethod("futuredate", function(value, element) {
		return this.optional(element) || Validator.getInstance().dateIsInFuture(value, 'mm/dd/yyyy');
	}, "Date must be in the future");

	jQuery.validator.addMethod("dateMMDDSlash", function(value, element) {
		return this.optional(element) || Validator.getInstance().dateMMDDSlash(value);
	}, "Date must be a valid date (MM/DD)");

	jQuery.validator.addMethod("stdDiscFieldGroup", function(value, element) {
		return validateStdDiscountFieldGroup(element, value);
	}, "Enter Discount Days, Day, or Date");

	jQuery.validator.addMethod("stdDueFieldGroup", function(value, element) {
		return validateStdDueFieldGroup(element, value);
	}, "Enter Due Days, Day, or Date");
	
	jQuery.validator.addMethod("stdOrderPercentTotal", function(value, element) {
		var percentTotal = formPtm.sumUpStdOrderPercents();
		var isFocused = element == document.activeElement;
		return this.optional(element) || value == 0 || (isFocused && percentTotal <= 100) || validatestdOrderPercentTotal();
	}, "Order Percent Must add up to 100");

	jQuery.validator.addMethod("eomThruDay", function(value, element) {
		var isFocused = element == document.activeElement;
		return this.optional(element) || (isFocused) || validateEomThruDay(element, value);
	}, "Invalid Thru Day");

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
							return formPtm.inputs.form.attr('data-code') == $('#code').val() ? 'false' : 'true';
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
			eom_due_day1: {
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

	$('.eom_thru_day').each(function() {
		let input = $(this);
		let parent = input.closest('.eom-day-range');

		input.rules( "add", {
			required: function() {
				return parent.find('.eom_from_day').val() != '';
			},
			min: function() {
				if (formPtm.inputs.fields.method.val() != codetable.config.methods.eom.value) {
					return 0;
				}
				return 0;
				// return parseInt(parent.find('.eom_from_day').val()) + 1;
			},
			eomThruDay: true,
		});
	});

	$('.eom_due_day').each(function() {
		let input = $(this);
		let parentEomSplit = input.closest('.eom-split');

		input.rules( "add", {
			required: function() {
				if (formPtm.inputs.fields.method.val() != codetable.config.methods.eom.value) {
					return false;
				}
				return parentEomSplit.find('.eom_thru_day').val() != '';
			},
		});
	});
});