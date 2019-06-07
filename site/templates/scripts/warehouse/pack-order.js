$(function() {
	var input_bin     = $('input[name=binID]');
	var input_barcode = $('input[name=barcode]');
	var input_qty     = $('input[name=qty]');
	var form_barcode  = $('form[id=barcode-form]');

	/**
	 * The Order of Functions based on Order of Events
	 * 1. Select / Enter Sales Order
	 * 2. Change Bin / Change Pallet
	 * 3. Finish Item / Exit Order
	 * 4. Remove Sales Order Locks
	 */

/////////////////////////////////////
// 1. Select / Enter Sales Order
////////////////////////////////////

	$("body").on("click", "#item-availability-modal .select-item", function(e) {
		e.preventDefault();
		var button    = $(this);
		var itemID    = button.data('itemid')
		var lotserial = String(button.data('lotserial'));

		if (input_barcode.length) {
			var barcode = lotserial.length > 0 ? lotserial : itemID;
			input_barcode.val(barcode);
		}

		if (!input_bin.length) {
			form_barcode.submit();
		}

		button.closest('.modal').modal('hide');
	});

/////////////////////////////////////
// 3. Finish Item / Exit Order
////////////////////////////////////
	$("body").on("click", ".finish-item", function(e) {
		e.preventDefault();
		var button = $(this);

		if (pickitem.item.qty.remaining < 0) {
			swal({
				title: 'Are you sure?',
				text: "You have picked too much",
				type: 'warning',
				confirmButtonClass: 'btn btn-success',
				buttonsStyling: false,
				confirmButtonText: 'Continue'
			});
		} else if (pickitem.item.qty.remaining > 0) {
			swal({
				title: 'Are you sure?',
				text: "You have not met the Quantity Requirements",
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
		} else {
			window.location.href = button.attr('href');
		}
	});

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
});
