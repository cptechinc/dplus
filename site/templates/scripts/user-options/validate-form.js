$(function() {
	let formUser = UserOptionsForm.getInstance();
	let alert    = UserOptionsAlerts.getInstance();
	let server   = UserOptionsRequests.getInstance();

	let momentJsFormats = {
		'mm/dd/yyyy': 'MM/DD/YYYY',
	}

/* =============================================================
	Form Validation
============================================================= */
	function validateDate(date) {
		if (date.length < 8) {
			return true;
		}
		let expiredate = moment(date, momentJsFormats['mm/dd/yyyy']);
		return expiredate.isValid();
	}

	jQuery.validator.addMethod("dateF", function(value, element) {
		return this.optional(element) || validateDate(value);
	}, "Date must be a valid date (MM/DD/YYYY)"); 

	let validator = formUser.form.validate({
		errorClass: "is-invalid",
		validClass: "is-valid",
		errorPlacement: function(error, element) {
			error.addClass('invalid-feedback');

			if (element.closest('.input-parent').length == 0) {
				error.insertAfter(element);
				return true;
			}
			error.appendTo(element.closest('.input-parent'));
		},
		rules: {
			userID: {
				required: true,
				remote: {
					url: config.ajax.urls.json + 'msa/validate/logm/id/',
					type: "get",
					data: {
						jqv: 'true',
					}
				}
			},
		},
		submitHandler: function(form) {
			$('#loading-modal').modal('show');
			form.submit();
		}
	});

	$('input.whseid').each(function() {
		let input = $(this);

		input.rules("add", {
			required: false,
			remote: {
				url: config.ajax.urls.json + 'min/validate/iwhm/code/',
				type: "get",
				data: {
					jqv: 'true',
					allowAll: 'true',
					id: function() {
						return input.val();
					}
				}
			}
		});
	});

	$('input.date').each(function() {
		let input = $(this);

		input.rules("add", {
			required: false,
			dateF: true,
		});
	});
});