$(function() {
	let formCode = CodeFormBase.getInstance();
	let formTrm  = TrmForm.getInstance();
	let alert    = CodeAlertsBase.getInstance();
	let server   = TrmRequests.getInstance();

/* =============================================================
	Unsaved Fields Alert
============================================================= */
	origForm = formCode.form.serialize();

	$("body").on("click", "a:not(#code-form .crud-submit, #ajax-modal a, .swal2-modal a, .bootstrap-select a)", function(e) {
		if (formCode.form.serialize() !== origForm) {
			e.preventDefault();
			var a = $(this);
			var href = a.attr('href');

			alert.unsavedChanges(function(save) {
				if (save) {
					formCode.form.find('button[type=submit]').click();
				} else {
					var uri = URI();
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
		var input = $(this);
		if (input.attr('tabindex') <= 116) {
			return true;
		}
		var form  = input.closest('form');
		var formEom = form.find('#eom-splits');

		var validator = form.validate();

		if (formEom.find('.is-invalid').length) {
			var invalidInput = formEom.find('.is-invalid');
			if (validator.element('#' + invalidInput.attr('id')) === false) {
				invalidInput.focus();
			}
			return true;
		}

		formEom.find('input').each(function() {
			var otherInput = $(this);

			if (validator.element('#' + otherInput.attr('id')) === false) {
				otherInput.focus();
				return true;
			}
		});
	});

	$("body").on("change", "#code-form select[name=method]", function(e) {
		var input = $(this);
		
		$('.type-input').removeClass('show');
		$('.type-splits').removeClass('show');

		if (input.val() == codetable.config.methods.std.value){
			$('[name="' + codetable.config.methods.std.typeInputName + '"]').addClass('show');
			$('#std-splits').addClass('show');
			return true;
		}

		if (input.val() == codetable.config.methods.eom.value){
			$('[name="' + codetable.config.methods.eom.typeInputName + '"]').addClass('show');
			$('#eom-splits').addClass('show');
			return true;
		}
	});

	$("body").on("change", "#code-form select[name=method]", function(e) {
		var input = $(this);
		
		$('.type-input').removeClass('show');
		$('.type-splits').removeClass('show');

		if (input.val() == codetable.config.methods.std.value){
			$('[name="' + codetable.config.methods.std.typeInputName + '"]').addClass('show');
			$('#std-splits').addClass('show');
			return true;
		}

		if (input.val() == codetable.config.methods.eom.value){
			$('[name="' + codetable.config.methods.eom.typeInputName + '"]').addClass('show');
			$('#eom-splits').addClass('show');
			return true;
		}
	});

	$("body").on("change", "#code-form input[name=ccprefix]", function(e) {
		var input = $(this);
		var parent = input.closest('.input-parent');
		var descriptionField = parent.find('.description');

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


/* =============================================================
	Method EOM Events
============================================================= */
	$("body").on("change", ".eom_disc_percent", function(e) {
		if (formCode.inputs.fields.method.val() != codetable.config.methods.eom.value){
			return false;
		}

		var input  = $(this);
		var percent = input.val() == '' ? 0 : parseFloat(input.val());
		
		if (percent == 0) {
			formTrm.enableDisableEomDiscDayMonthFromPercent(input);
			return true;
		}
		input.val(percent.toFixed(formCode.config.fields.eom_disc_percent.precision));
		formTrm.enableDisableEomDiscDayMonthFromPercent(input);
	});

	$("body").on("change", ".eom_thru_day", function(e) {
		if (formCode.inputs.fields.method.val() != codetable.config.methods.eom.value) {
			return false;
		}

		var input  = $(this);
		
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
	var validator = formCode.form.validate({
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
			// code: {
			// 	required: true,
			// 	maxlength: formCode.config.fields.code.maxlength,
			// 	remote: {
			// 		url: codetable.config.urls.api.validate,
			// 		type: "get",
			// 		data: {
			// 			jqv: 'true',
			// 			new: function() {
			// 				return formCode.inputs.form.attr('data-code') == $('#code').val() ? 'false' : 'true';
			// 			},
			// 		}
			// 	}
			// },
			eom_due_day1: {
				required: true,
			}
		},
		submitHandler: function(form) {
		// 	var formCode = CodeFormBase.getInstance();
		// 	var server   = CodeRequestsBase.getInstance();
		// 	var code     = formCode.inputs.fields.code.val();

		// 	if (formCode.inputs.form.attr('data-code') == code) {
		// 		form.submit();
		// 	} else {
		// 		server.validateCode(code, function(exists) {
		// 			var alert = CodeAlertsBase.getInstance();

		// 			if (exists === false) {
		// 				form.submit();
		// 				return true;
		// 			}

		// 			alert.codeExists(code, function(editCode) {
		// 				if (editCode) {
		// 					var uri = URI();
		// 					uri.setQuery('code', code);
		// 					window.location.href = uri.toString();
		// 				} else {
		// 					location.reload();
		// 				}
		// 			});
		// 		});
		// 	}
		}
	});

	$('.eom_due_day').each(function() {
		var input = $(this);
		var parentEomSplit = input.closest('.eom-split');

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
		var input = $(this);
		var parent = input.closest('.eom-day-range');
		var parentEomSplit = input.closest('.eom-split');
		var min = 2;

		input.rules( "add", {
			min: function() {
				if (formCode.inputs.fields.method.val() != codetable.config.methods.eom.value) {
					return 0;
				}
				return parseInt(parent.find('.eom_from_day').val()) + 1;
			}
		});
	});
});