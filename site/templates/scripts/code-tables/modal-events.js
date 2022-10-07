$(function() {
	var alarm = Alerts.getInstance();

	$('#edit-code-modal').on('shown.bs.modal', function (event) {
		var button = $(event.relatedTarget); // Button that triggered the modal
		var modal  = $(this);
		var form   = modal.find('form');
		var code   = button.data('code');

		if (code) {
			if (form.find('input[name=description]').length) {
				form.find('input[name=description]').focus();
				return true;
			}
			if (form.find('input[name=name]').length) {
				form.find('input[name=name]').focus();
			}
		} else {
			form.find('input[name=code]').focus();
		}
		form.attr('data-serialized', form.serialize());
	});

	$("#edit-code-modal").on('hide.bs.modal', function (e) {
		var modal = $(this);
		var form = modal.find('form');
		var originalValues = form.attr('data-serialized');

		if (originalValues) {
			if (originalValues != form.serialize()) {
				e.preventDefault();

				alarm.unsavedChanges(function(confirmSave) {
					if (confirmSave) {
						form.submit();
						return true;
					}
					form.attr('data-serialized', '');
					modal.modal('hide');
				});
			}
		}
	});

	$("#edit-code-modal").on('hidden.bs.modal', function (e) {
		var modal = $(this);
		var form = modal.find('form');

		var validator = form.validate();

		if (validator) {
			validator.resetForm();
		}
		
		form.find('.is-invalid').removeClass("is-invalid");
		form.find('.is-valid').removeClass("is-valid");
		form.find('.invalid-feedback').remove();
		form.find('.valid-feedback').remove();

		if (form.attr('data-code')) {
			$('.code-table-alert').remove();
			$('.bg-success').removeClass('bg-success text-white');
			$('.highlight').removeClass('highlight');
			$('.code[data-code="'+form.attr('data-code')+'"]').addClass('highlight');
			// $('html, body').animate({scrollTop: $('.code[data-code="'+form.attr('data-code')+'"]').offset().top,},700,'linear');
		}
	});
});