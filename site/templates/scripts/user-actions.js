$(function() {
	var form_task = $('#task-form');
	var modal_task = $('#complete-task-modal');

	modal_task.on('shown.bs.modal', function (event) {
		var button = $(event.relatedTarget);
		var modal = $(this);
		modal.find('#reflectnote').focus();
	})
});
