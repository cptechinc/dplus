$(function() {
	var form = $('#add-qnote-form');
	var input_linenbr          = form.find("input[name=linenbr]");
	var input_sequence         = form.find("input[name=sequence]");
	var input_notes            = form.find("textarea[name=notes]");
	var input_pick             = form.find("input[name=check_pick]");
	var input_pack             = form.find("input[name=check_pack]");
	var input_invoice          = form.find("input[name=check_invoice]");
	var input_acknwoledgement  = form.find("input[name=check_acknowledgement]");

	$('#add-note-modal').on('show.bs.modal', function (event) {
		var button = $(event.relatedTarget);
		var linenbr = button.data('linenbr');
		var sequence = button.data('sequence');
		var modal = $(this);
		var edit_desc = '';

		input_linenbr.val(linenbr);
		input_sequence.val(sequence);

		if (parseInt(linenbr) > 0) {
			edit_desc = 'Line #'+linenbr;
		} else {
			edit_desc = "Header";
		}

		if (parseInt(sequence) > 0) {
			// Note exists, must find values and set them accordingly
			modal.find('.modal-title').text('Editing '+edit_desc+' note').val();
			var row_qnote = $('.qnote[data-linenbr="'+linenbr+'"][data-sequence="'+sequence+'"]');
			var value_notes            = row_qnote.find(".notes").text();
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
			modal.find('.modal-title').text('Adding '+edit_desc+' note');
		}
	});
});
