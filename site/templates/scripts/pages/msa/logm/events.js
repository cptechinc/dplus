$(function() {
	let server	 = LogmRequests.getInstance();
	let alert	 = LogmAlerts.getInstance();
	let formLogm = LogmForm.getInstance();

/* =============================================================
	Unsaved Changes Alert
============================================================= */
	origForm = formLogm.form.serialize();

	$("body").on("click", "a:not(#logm-form .crud-submit, #ajax-modal a)", function(e) {
		if (formLogm.form.serialize() !== origForm) {
			e.preventDefault();
			let a = $(this);
			let href = a.attr('href');

			alert.unsavedChanges(function(saveChanges) {
				if (saveChanges) {
					formLogm.form.find('button[type=submit]').click();
				} else {
					// Send HTTP GET Request to remove Record Lock
					let uri = URI();
					let query = uri.query(true);
					uri.query({focus: query.id});

					$.get(uri.toString(), function() {
						window.location.href = href;
					});
				}
			});
		}
	});

/* =============================================================
	Input Focus events
============================================================= */
	$("body").on("focusin", "#logm-form input:not(input[name=id])", function(e) {
		if (formLogm.inputs.loginid.val() == '') {
			formLogm.inputs.loginid.focus();
		}
	});

/* =============================================================
	Input Change events
============================================================= */
	$("body").on("change", "input[data-jqv=true]", function(e) {
		let input = $(this);

		if (input.val().trim() == '') {
			input.val(input.val().trim());
		}
	});

	$("body").on("change", 'input[type="number"]', function(e) {
		let input = $(this);
		let value = input.val();
		input.val(value.replace(/\s/g, ''));
	});

	$("body").on("change", "input[name=id]", function(e) {
		let input = $(this);
		let id = input.val();
		
		server.validateId(id, function(exists) {
			if (exists) {
				alert.exists(id, function(editUser) {
					if (editUser) {
						let uri = URI();
						uri.setQuery('id', id);
						window.location.href = uri.toString();
						return true;
					}
					location.reload();
				});
			}
		});
	});

	$("body").on("change", "input[name=whseid]", function(e) {
		let input = $(this);
		let descriptionField = input.closest('.input-parent').find('.ajax-description');
		descriptionField.text('');

		if (input.val() == '') {
			return true;
		}
		
		server.getWarehouse(input.val(), function(whse) {
			if (whse) {
				descriptionField.text(whse.name);
			}
		});
	});

	$("body").on("change", "input.printer", function(e) {
		let input = $(this);
		let descriptionField = input.closest('.input-parent').find('.ajax-description');
		descriptionField.text('');

		if (input.val() == '') {
			return true;
		}
		
		server.getPrinter(input.val(), function(printer) {
			if (printer) {
				descriptionField.text(printer.description);
			}
		});
	});

	$("body").on("change", "input[name=groupid]", function(e) {
		let input = $(this);
		let descriptionField = input.closest('.input-parent').find('.ajax-description');
		descriptionField.text('');

		if (input.val() == '') {
			return true;
		}
		
		server.getLoginGroup(input.val(), function(group) {
			if (group) {
				descriptionField.text(group.description);
			}
		});
	});

	$("body").on("change", "input[name=roleid]", function(e) {
		let input = $(this);
		let descriptionField = input.closest('.input-parent').find('.ajax-description');
		descriptionField.text('');

		if (input.val() == '') {
			return true;
		}
		
		server.getLoginRole(input.val(), function(role) {
			if (role) {
				descriptionField.text(role.description);
			}
		});
	});
	

/* =============================================================
	AJAX Lookup Modal Functions
============================================================= */
	$("body").on('click', '#ajax-modal .whse-link', function(e) {
		e.preventDefault();
		let button = $(this);
		let whseid = button.data('whseid');
		let modal  = button.closest('.modal');
		let input  = $(modal.attr('data-input'));
		input.val(whseid);
		modal.modal('hide');
	});

	$("#ajax-modal").on('hidden.bs.modal', function (e) {
		let modal = $(this);

		if (modal.attr('data-input')) {
			let input = $(modal.attr('data-input'));
			if (input.length) {
				input.focus();
			}
		}
	});
});