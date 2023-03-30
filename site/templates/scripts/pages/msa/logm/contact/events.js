$(function() {
	let modalContact = $('#contact-modal');
	let formContact  = modalContact.find('form');

/* =============================================================
	Unsaved Changes Alert
============================================================= */
	let origForm = formContact.serialize();

	modalContact.on('hide.bs.modal', function (e) {
		let modal = $(this);
		let form = modal.find('form');

		if (formContact.serialize() !== origForm) {
			e.preventDefault();

			alert.unsavedChanges(function(confirmSave) {
				if (confirmSave) {
					form.submit();
					return true;
				}
				location.reload();
				modal.modal('hide');
			});
		}
	});

/* =============================================================
	Modal events
============================================================= */
	modalContact.on('shown.bs.modal', function (event) {
		let modal  = $(this);
		let form   = modal.find('form');

		form.find('input[name=faxname]').focus();
	});
});