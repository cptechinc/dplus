$(function() {
	let formUser = UserOptionsForm.getInstance();
	let alert    = UserOptionsAlerts.getInstance();
	let server   = UserOptionsRequests.getInstance();

	if (formUser.inputs.fields.userid.val().trim() != '') {
		let keys = Object.keys(formUser.inputs.fields);
		let key =  keys[1];
		formUser.inputs.fields[key].focus();
	}

	if (formUser.inputs.fields.userid.val() == '') {
		formUser.inputs.fields.userid.focus();
	}

/* =============================================================
	Unsaved Fields Alert
============================================================= */
	origForm = formUser.form.serialize();

	$("body").on("click", "a:not(#user-form .crud-submit, #ajax-modal a, .swal2-modal a, .bootstrap-select a, #user-form .delete_button)", function(e) {
		if (formUser.form.serialize() !== origForm) {
			e.preventDefault();
			let a = $(this);
			let href = a.attr('href');

			alert.unsavedChanges(function(save) {
				if (save) {
					let validator = formUser.form.validate();
					if (validator.valid()) {
						formUser.form.find('button[type=submit]').click();
					}
				} else {
					let uri = URI();
					uri.setQuery('userID', '');

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
	$("body").on("focusin", "#user-form input:not(input[name=userID]), #user-form select", function(e) {
		if (formUser.inputs.fields.userid.val() == '') {
			formUser.inputs.fields.userid.focus();
		}
		if (formUser.form.validate().element('#' + formUser.inputs.fields.userid.attr('id')) === false) {
			formUser.inputs.fields.userid.focus();
		}
	});

	$("body").on("change", "#user-form input[name=userID]", function(e) {
		let input = $(this);
		let nameField = input.closest('.input-parent').find('.name');

		if (input.val() == '') {
			nameField.text('');
			return false;
		}

		server.userOptionsExists(input.val(), function(exists) {
			if (exists === true) {
				alert.userExists(input.val(), function(editCode) {
					if (editCode) {
						let uri = URI();
						uri.setQuery('userID', input.val());
						window.location.href = uri.toString();
					} else {
						location.reload();
					}
				});
				return true;
			}
			server.getLogmUser(input.val(), function(user) {
				if (user) {
					nameField.text(user.name);
					return true;
				}
				input.focus();
			});
		});
	});

	$("body").on("change", "#user-form input.whseid", function(e) {
		let input = $(this);
		// let nameField = input.closest('.input-parent').find('.name');

		if (input.val().trim() == '') {
			// nameField.text('');
			input.val('**');
			return false;
		}

		// server.getWarehouse(input.val(), function(warehouse) {
		// 	nameField.text(warehouse.name);
		// });
	});

	$("body").on("keyup", "#user-form input.days", function(e) {
		var input = $(this);

		if (input.val().trim() == '') {
			input.val(input.val().trim());
		}
	});


	$("body").on("keyup", "#user-form input.date", function(e) {
		let input = $(this);

		if (input.val().trim() == '') {
			input.val('');
		}
	});

	$("body").on("change", "#user-form input.date", function(e) {
		let input = $(this);

		let date = new DateFormatter(input.val(), 'mm/dd/yyyy');
		date.updateCentury();

		if (date.isValid()) {
			input.val(date.format('mm/dd/yyyy'));
		}
	});

	$("body").on('click', '#ajax-modal .user-link', function(e) {
		e.preventDefault();
		var button = $(this);
		var modal  = button.closest('.modal');
		var input  = $(modal.attr('data-input'));
		input.val(button.data('userid'));
		if (input.data('jqv')) {
			input.change();
		}
		modal.modal('hide');
	});

	$("body").on('click', '#ajax-modal .whse-link', function(e) {
		e.preventDefault();
		var button = $(this);
		var modal  = button.closest('.modal');
		var input  = $(modal.attr('data-input'));
		input.val(button.data('whseid'));
		if (input.data('jqv')) {
			input.change();
		}
		modal.modal('hide');
	});
});