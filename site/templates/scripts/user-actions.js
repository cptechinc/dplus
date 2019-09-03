$.fn.extend({
	validate_title: function() {
		var form = $(this);
		var input_title = form.find('input[name=title]');
		var error = false;
		var title = '';
		var msg = '';
		var html = false;

		if (input_title.val() == '') {
			error = true;
			title = 'Error';
			msg = 'Please enter a Title';

		}
		return new SwalError(error, title, msg, html);
	},
	validate_duedate: function() {
		var form = $(this);
		var input_date = form.find('input[name=duedate]');
		var error = false;
		var title = '';
		var msg = '';
		var html = false;
		var regex = /((0|1)\d{1})\/((0|1|2)\d{1})\/((19|20)\d{2})/g;
		var value = input_date.val();

		if (input_date.val() == '') {
			error = true;
			title = 'Error';
			msg   = 'Please enter a due date';
		} else if (!value.match(regex)) {
			error = true;
			title = 'Error';
			msg   = 'Please enter the date in MM/DD/YYYY format';
		}
		return new SwalError(error, title, msg, html);
	},
	validate_subtype: function() {
		var form = $(this);
		var input_subtype = form.find('input[name=subtype]');
		var error = false;
		var title = '';
		var msg = '';
		var html = false;

		if (input_subtype.val() == '') {
			error = true;
			title = 'Error';
			msg = 'Please choose a subtype';

		}
		return new SwalError(error, title, msg, html);
	},
});


$(function() {
	var form_task = $('#task-form');
	var form_task_create   = $("#create-task-form");
	var form_note_create   = $("#create-note-form");
	var form_action_create = $("#create-action-form");
	var modal_task = $('#complete-task-modal');

	modal_task.on('shown.bs.modal', function (event) {
		var button = $(event.relatedTarget);
		var modal = $(this);
		modal.find('#reflectnote').focus();
	})


	$('.action-subtype-select').on('change', function (e) {
		e.preventDefault();
		var select = $(this);
		var option = select.find('option:selected');
		var iconclass = option.data('icon');
		var inputgroup = select.parent('.input-group');
		var icon_element = inputgroup.find('.input-group-text').find('i');
		icon_element.removeClass(icon_element.attr('class'));
		icon_element.addClass(iconclass);
	});

	if (form_task_create.length) {
		form_task_create.validate({
			errorClass: "is-invalid",
			validClass: "is-valid",
			errorPlacement: function(error, element) {
				if (element.hasParent('.input-group')) {
					var parent = element.closest('.input-group');
					error.insertAfter(parent).addClass('invalid-feedback');
				} else {
					error.insertAfter(element);
				}
			},
			submitHandler : function(form) {
				var valid_title = form_task_create.validate_title();
				var valid_duedate = form_task_create.validate_duedate();
				var valid_subtype = form_task_create.validate_subtype();
				var valid_form = new SwalError(false, '', '', false);

				if (valid_title.error) {
					valid_form = valid_title;
				} else if (valid_duedate.error) {
					valid_form = valid_duedate;
				} else if (valid_subtype.error) {
					valid_form = valid_subtype;
				}

				if (valid_form.error) {
					swal({
						type: 'error',
						title: valid_form.title,
						text: valid_form.msg,
						html: valid_form.html
					});
				} else {
					form.submit();
				}
			}
		});
	}

	if (form_note_create.length) {
		form_note_create.validate({
			errorClass: "is-invalid",
			validClass: "is-valid",
			errorPlacement: function(error, element) {
				if (element.hasParent('.input-group')) {
					var parent = element.closest('.input-group');
					error.insertAfter(parent).addClass('invalid-feedback');
				} else {
					error.insertAfter(element);
				}
			},
			submitHandler : function(form) {
				var valid_title = form_note_create.validate_title();
				var valid_subtype = form_note_create.validate_subtype();
				var valid_form = new SwalError(false, '', '', false);

				if (valid_title.error) {
					valid_form = valid_title;
				} else if (valid_subtype.error) {
					valid_form = valid_subtype;
				}

				if (valid_form.error) {
					swal({
						type: 'error',
						title: valid_form.title,
						text: valid_form.msg,
						html: valid_form.html
					});
				} else {
					form.submit();
				}
			}
		});
	}

	if (form_action_create.length) {
		form_action_create.validate({
			errorClass: "is-invalid",
			validClass: "is-valid",
			errorPlacement: function(error, element) {
				if (element.hasParent('.input-group')) {
					var parent = element.closest('.input-group');
					error.insertAfter(parent).addClass('invalid-feedback');
				} else {
					error.insertAfter(element);
				}
			},
			submitHandler : function(form) {
				var valid_title = form_note_create.validate_title();
				var valid_subtype = form_note_create.validate_subtype();
				var valid_form = new SwalError(false, '', '', false);

				if (valid_title.error) {
					valid_form = valid_title;
				} else if (valid_subtype.error) {
					valid_form = valid_subtype;
				}

				if (valid_form.error) {
					swal({
						type: 'error',
						title: valid_form.title,
						text: valid_form.msg,
						html: valid_form.html
					});
				} else {
					form.submit();
				}
			}
		});
	}
});
