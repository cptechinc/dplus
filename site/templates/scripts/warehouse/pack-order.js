$(function() {
	var itemtype_serialized = 'S';
	var itemtype_lotted = 'L';

	var input_barcode = $('input[name=barcode]');
	var input_qty     = $('input[name=qty]');
	var input_linenbr = $('input[name=linenbr]');
	var form_barcode  = $('form[id=barcode-form]');

	/////////////////////////////////////
	// Table of Contents
	/////////////////////////////////////
	// 1. Choose Item to fill packing form
	// 2. Exit Order
	// 3. Finish Order
	// 4. Print Invoice / Pack Ticket
	////////////////////////////////////

	/* ===============  1. Choose Item to fill packing form ========================== */

	$("body").on("click", ".choose-item", function(e) {
		e.preventDefault();
		var button = $(this);
		var itemID = button.data('itemid');
		var linenbr = button.data('linenbr');
		var itemtype = button.data('itemtype');
		var lotserial = button.data('lotserial');

		input_linenbr.val(linenbr);

		if (itemtype == itemtype_serialized) {
			input_barcode.val('').focus();
		} else if (itemtype == itemtype_lotted) {
			input_barcode.val(lotserial);
			input_barcode.focus();
		} else {
			input_barcode.val(itemID);
		}
	});

	/* ===============  2. Exit Order ========================== */

	$("body").on("click", ".exit-order", function(e) {
		e.preventDefault();
		var button = $(this);

		swal({
			title: 'Are you sure?',
			text: "You are trying to leave this order",
			type: 'warning',
			showCancelButton: true,
			confirmButtonClass: 'btn btn-success',
			cancelButtonClass: 'btn btn-danger',
			buttonsStyling: false,
			confirmButtonText: 'Yes!'
		}).then(function (result) {
			if (result) {
				window.location.href = button.attr('href');
			}
		}).catch(swal.noop);
	});

	/* ===============  3. Finish Order ========================== */

	$("body").on("click", ".finish-order", function(e) {
		e.preventDefault();
		var button = $(this);

		swal({
			title: 'Are you sure?',
			text: "You are trying to submit this order",
			type: 'warning',
			showCancelButton: true,
			confirmButtonClass: 'btn btn-success',
			cancelButtonClass: 'btn btn-danger',
			buttonsStyling: false,
			confirmButtonText: 'Yes!'
		}).then(function (result) {
			if (result) {
				window.location.href = button.attr('href');
			}
		}).catch(swal.noop);
	});

	/* ===============  4. Print Invoice / Pack Ticket ========================== */

	var printlabelform = $('#print-form');

	$("body").on("click", ".printer-check", function(e) {
		var checkbox = $(this);
		var printer = checkbox.data('printer');
		var printerdiv = $('#'+printer);

		if (checkbox.is(':checked')) {
			printerdiv.addClass('show');
		} else {
			printerdiv.removeClass('show');
		}
	});

	$('#labelprinters-modal').on('show.bs.modal', function (event) {
		var button = $(event.relatedTarget); // Button that triggered the modal
		var inputname = button.data('input'); // Extract info from data-* attributes
		var modal = $(this);
		modal.attr('data-input', inputname);
	});

	$("body").on("click", ".select-labelprinter", function(e) {
		e.preventDefault();
		var button  = $(this);
		var labelID = button.find('.printer-id').text();
		var desc    = button.find('.printer-desc').text();
		var modal   = button.closest('.modal');
		var inputname = modal.attr('data-input');
		var input = printlabelform.find('input[name="'+inputname+'"]');
		input.val(labelID);
		input.closest('.printer-input').find('.printer-desc').text(desc);
		modal.modal('hide');
	});
});
