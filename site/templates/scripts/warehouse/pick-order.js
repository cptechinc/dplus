$(function() {
	var input_bin     = $('input[name=binID]');
	var input_barcode = $('input[name=barcode]');
	var input_qty     = $('input[name=qty]');
	var form_barcode  = $('form[id=barcode-form]');
	var modal_ajax = $('#ajax-modal');

	/**
	 * The Order of Functions based on Order of Events
	 * 1. Select / Enter Sales Order
	 * 2. Change Bin / Change Pallet
	 * 3. Finish Item / Exit Order
	 * 4. Remove Sales Order Locks
	 */

var uri = URI();
var query = uri.query(true);

if (query.hasOwnProperty('scan') === false) {
	var input = $('input[name=scan]');
	input.focus();
	$('html, body').animate({scrollTop: input.offset().top,},700,'linear');
}

/////////////////////////////////////
// 1. Select / Enter Sales Order
////////////////////////////////////

/////////////////////////////////////
// 2. Change Bin / Change Pallet
//
////////////////////////////////////

/* =============================================================
	Lookup Modal Functions
============================================================= */
	modal_ajax.on('show.bs.modal', function (event) {
		var button = $(event.relatedTarget); // Button that triggered the modal
		var modal = $(this);
		var url = button.data('lookupurl');

		modal.attr('data-input', button.data('input'));
		modal.find('.modal-title').text(button.attr('title'));
		modal.resizeModal('xl');
		modal.find('.modal-body').loadin(url, function() {});
	});

	$("body").on('click', '.item-link', function(e) {
		e.preventDefault();
		var button = $(this);
		var itemID = button.data('itemid');
		var modal = button.closest('.modal');

		$(modal.attr('data-input')).val(itemID);
		modal.modal('hide');
	});

	$("body").on('submit', '#ajax-modal form', function(e) {
		e.preventDefault();
		var form = $(this);
		var query = form.serialize();
		var action = form.attr('action');
		var search = form.find('input[name=q]').val();
		var url = action + '?' + query;
		form.closest('.modal').find('.modal-title').text('Searching for ' + search);
		form.closest('.modal').find('.modal-body').loadin(url, function() {});
	});

	$("body").on('click', '#ajax-modal .paginator-link', function(e) {
		e.preventDefault();
		var href = $(this).attr('href');
		modal_ajax.find('.modal-body').load(href);
	});

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
			swal2.fire({
				title: 'Are you sure?',
				text: "You are trying to leave this bin without fulfilling bin item",
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Yes!'
			}).then(function (result) {
				if (result.value) {
					swal_changebin();
				}
			});
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

	$("body").on("click", "#item-availability-modal .select-item", function(e) {
		e.preventDefault();
		var button    = $(this);
		var itemID    = button.data('itemid')
		var lotserial = String(button.data('lotserial'));
		var qtyavailable = parseInt(button.data('available'));
		var qty  = qtyavailable > pickitem.item.qty.remaining ? pickitem.item.qty.remaining : qtyavailable;

		if (input_bin.length) {
			var binID = button.data('bin');
			input_bin.val(binID);
		}

		if (input_barcode.length) {
			var barcode = lotserial.length > 0 ? lotserial : itemID;
			input_barcode.val(barcode);
		}

		input_qty.val(pickitem.item.qty.remaining);

		if (!input_bin.length) {
			form_barcode.submit();
		}

		button.closest('.modal').modal('hide');
	});

	$("body").on("click", "#item-availability-modal .select-bin", function(e) {
		e.preventDefault();
		var button    = $(this);

		if (input_bin.length) {
			var binID = button.data('bin');
			input_bin.val(binID);
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
			swal2.fire({
				title: 'Are you sure?',
				text: "You have picked too much",
				icon: 'warning',
				confirmButtonText: 'Continue'
			});
		} else if (pickitem.item.qty.remaining > 0) {
			swal2.fire({
				title: 'Are you sure?',
				text: "You have not met the Quantity Requirements",
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Yes!'
			}).then(function (result) {
				if (result.value) {
					window.location.href = button.attr('href');
				}
			});
		} else {
			window.location.href = button.attr('href');
		}
	});

	$("body").on("click", ".exit-order", function(e) {
		e.preventDefault();
		var button = $(this);

		swal2.fire({
			title: 'Are you sure?',
			text: "You are trying to leave this order",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: 'Yes!'
		}).then(function (result) {
			if (result) {
				window.location.href = button.attr('href');
			}
		});
	});

	/////////////////////////////////////
	// 4. Remove Sales Order Locks
	////////////////////////////////////
	$('#so-lock-div').on('shown.bs.collapse', function () {
		var form = $(this).find('form');
		form.find('input[name=ordn]').focus();
	});

	$("#add-lotserials-form").validate({
		submitHandler : function(form) {
			var jForm = $(form);
			jForm.find('input[name="lotserialbins[]"]:not(:checked)').each(function() {
				var checkInput = $(this);
				var parent = checkInput.closest('.lotserial-record');

				parent.find('input').each(function() {
					var input = $(this);
					input.attr('disabled', 'disabled');
				});
			});
			form.submit();
		}
	});

});

function swal_changebin() {
	swal2.fire({
		title: "Enter the Bin you'd like to change to",
		text: "Bin ID",
		input: 'text',
		showCancelButton: true,
		inputValidator: function (value) {
			return new Promise(function (resolve, reject) {
				if (value) {
					resolve();
				} else {
					resolve('You need to write something!');
				}
			})
		}
	}).then(function (input) {
		if (input.value) {
			var binID = input.value;
			var pageurl = URI();
			var uri = URI(pickitem.url_changebin).addQuery('binID', binID);
			window.location.href = uri.toString();
		}
	});
}
