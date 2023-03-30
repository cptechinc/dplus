$(function() {
	let formsPassword  = $('.logm-password-form');

/* =============================================================
	jQuery Validate Form
============================================================= */
	formsPassword.each(function() {
		let form = $(this);

		form.validate({
			onkeyup: false,
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
	
			},
			submitHandler: function(form) {
				$('#loading-modal').modal('show');
				form.submit();
			}
		});
	});
});