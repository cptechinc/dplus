$(function() {
	let formUser = UserOptionsForm.getInstance();
	let alert    = UserOptionsAlerts.getInstance();
	let server   = UserOptionsRequests.getInstance();

/* =============================================================
	Form Validation
============================================================= */
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
			form.submit();
		}
	});
});