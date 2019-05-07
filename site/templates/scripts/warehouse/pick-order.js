$(function() {
	var input_bin = $('input[name=binID]');
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

/////////////////////////////////////
// 2. Change Bin / Change Pallet
////////////////////////////////////
	$("body").on("change", ".change-pallet", function(e) {
		e.preventDefault();
		var select = $(this);
		var form = select.parent('form');
		form.submit();
	});

	$("body").on("click", ".change-bin", function(e) {
		e.preventDefault();
		var button = $(this);

		if (pickitem.item.qty.remaining > 0 && pickitem.item.qty.total_picked > 0) {
			swal({
				title: 'Are you sure?',
				text: "You are trying to leave this bin without fulfilling bin item",
				type: 'warning',
				showCancelButton: true,
				confirmButtonClass: 'btn btn-success',
				cancelButtonClass: 'btn btn-danger',
				buttonsStyling: false,
				confirmButtonText: 'Yes!'
			}).then(function (result) {
				if (result) {
					swal_changebin();
				}
			}).catch(swal.noop);
		} else {
			swal_changebin();
		}
	});

	$("body").on("click", "#bins-modal .choose-bin", function(e) {
		e.preventDefault();
		var button = $(this);
		var binID = button.data('bin');
		input_bin.val(binID);
		button.closest('.modal').modal('hide');
	});

/////////////////////////////////////
// 3. Finish Item / Exit Order
////////////////////////////////////
	$("body").on("click", ".finish-item", function(e) {
		e.preventDefault();
		var button = $(this);

		if (pickitem.item.qty.expected < pickitem.item.qty.picked) {
			swal({
				title: 'Bin Error',
				text: "You have picked more than expected bin qty",
				type: 'warning',
				confirmButtonClass: 'btn btn-success',
				buttonsStyling: false,
				confirmButtonText: 'Continue'
			});
		} else if (pickitem.item.qty.remaining < 0) {
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
				text: "You have not met the Quantity Requirments",
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

	/////////////////////////////////////
	// 4. Remove Sales Order Locks
	////////////////////////////////////
	$('#so-lock-div').on('shown.bs.collapse', function () {
		var form = $(this).find('form');
		form.find('input[name=ordn]').focus();
	});

});

function swal_changebin() {
	swal({
		title: "Enter the Bin you'd like to change to",
		text: "Bin ID",
		input: 'text',
		showCancelButton: true,
		inputValidator: function (value) {
			return new Promise(function (resolve, reject) {
				if (value) {
					resolve();
				} else {
					reject('You need to write something!');
				}
			})
		}
	}).then(function (input) {
		if (input) {
			var binID = input;
			var pageurl = URI();
			var uri = URI(pickitem.url_changebin).addQuery('binID', binID);
			window.location.href = uri.toString();
		}
	}).catch(swal.noop);
}
