$(function() {
	var form = $('#qnote-form');
	var input_linenbr          = form.find("input[name=linenbr]");
	var input_form             = form.find("input[name=form]");
	var input_notes            = form.find("textarea[name=note]");
	var input_pick             = form.find("input[name=check_pick]");
	var input_pack             = form.find("input[name=check_pack]");
	var input_invoice          = form.find("input[name=check_invoice]");
	var input_acknwoledgement  = form.find("input[name=check_acknowledgement]");

	$('#note-modal').on('shown.bs.modal', function (event) {
		var button = $(event.relatedTarget) // Button that triggered the modal
		var modal = $(this);
		modal.find('textarea[name=note]').focus();
	});

	$('#note-modal').on('show.bs.modal', function (event) {
		var button = $(event.relatedTarget);
		var modal = $(this);
		var linenbr = button.data('linenbr');
		var qnote_form = button.data('form');

		var edit_desc = '';

		input_linenbr.val(linenbr);
		input_form.val(qnote_form);

		if (parseInt(linenbr) > 0) {
			edit_desc = 'Line #'+linenbr;
		} else {
			edit_desc = "Header";
		}

		if (qnote_form != '') {
			// Note exists, must find values and set them accordingly
			modal.find('.modal-title').text('Editing '+edit_desc+' Note').val();
			var row_qnote = $('.qnote-row[data-linenbr="'+linenbr+'"][data-form="'+qnote_form+'"]');
			var value_notes            = row_qnote.find("textarea").text();
			var value_pick             = row_qnote.find(".check-pick").text() == 'Y';
			var value_pack             = row_qnote.find(".check-pack").text() == 'Y';
			var value_invoice          = row_qnote.find(".check-invoice").text() == 'Y';
			var value_acknwoledgement  = row_qnote.find(".check-acknowledgement").text() == 'Y';

			input_notes.text(value_notes);
			input_pick.prop('checked', value_pick);
			input_pack.prop('checked', value_pack);
			input_invoice.prop('checked', value_invoice);
			input_acknwoledgement.prop('checked', value_acknwoledgement);
		} else {
			modal.find('.modal-title').text('Adding '+edit_desc+' Note');
		}
	});
});
