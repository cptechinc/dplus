$(function() {
	var formPricing = $('#pricing-itemid-form');

/* =============================================================
	Validation Functions
============================================================= */
	formPricing.validate({
		errorClass: "is-invalid",
		validClass: "is-valid",
		errorPlacement: function(error, element) {
			error.insertAfter(element).addClass('invalid-feedback');
		},
		rules: {
			itemID: {
				required: true,
				remote: {
					url: config.ajax.urls.json + 'inv/validate/itemid/',
					type: "get",
					data: {
						jqv: 'true'
					}
				}
			},
		},
		messages: {},
		submitHandler: function(form) {
			form.submit();
		}
	});



});
