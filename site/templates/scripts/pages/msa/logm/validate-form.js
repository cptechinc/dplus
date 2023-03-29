$(function() {
	let formLogm = LogmForm.getInstance();

/* =============================================================
	jQuery Validate Form
============================================================= */
	formLogm.form.validate({
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
			whseid: {
				required: true,
				remote: {
					url: config.ajax.urls.json + 'min/validate/iwhm/code/',
					type: "get",
					data: {
						jqv: 'true',
						id: function() {
							return $('#whseid').val();
						}
					}
				}
			},
			printerreport: {
				required: true,
				remote: {
					url: config.ajax.urls.json + 'msa/validate/printer/id/',
					type: "get",
					data: {
						jqv: 'true',
						id: function() {
							return $('#printerreport').val();
						}
					}
				}
			},
			printerbrowse: {
				required: true,
				remote: {
					url: config.ajax.urls.json + 'msa/validate/printer/id/',
					type: "get",
					data: {
						jqv: 'true',
						id: function() {
							return $('#printerbrowse').val();
						}
					}
				}
			},
			groupid: {
				required: false,
				remote: {
					url: config.ajax.urls.json + 'msa/validate/lgrp/code/',
					type: "get",
					data: {
						jqv: 'true',
						code: function() {
							return $('#groupid').val();
						}
					}
				}
			},
			roleid: {
				required: false,
				remote: {
					url: config.ajax.urls.json + 'msa/validate/lrole/id/',
					type: "get",
					data: {
						jqv: 'true',
						id: function() {
							return $('#roleid').val();
						}
					}
				}
			},
		},
		submitHandler: function(form) {
			$('#loading-modal').modal('show');
			form.submit();
		}
	});
});