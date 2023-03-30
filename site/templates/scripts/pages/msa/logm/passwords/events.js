$(function() {
	let modalsPassword = $('.password-modal');
	let alert	     = LogmAlerts.getInstance();

/* =============================================================
	Unsaved Changes Alert
============================================================= */
	modalsPassword.on('hide.bs.modal', function (e) {
		let modal = $(this);
		let form = modal.find('form');

		if (form.find('input[name=password]').val() == '') {
			return true;
		}

		alert.unsavedChanges(function(confirmSave) {
			if (confirmSave) {
				form.submit();
				return true;
			}
			form.find('input[name=password]').val('');
		});
	});

/* =============================================================
	Modal events
============================================================= */
	modalsPassword.on('shown.bs.modal', function (event) {
		let modal  = $(this);
		let form   = modal.find('form');

		form.find('input[name=password]').focus();
	});
});