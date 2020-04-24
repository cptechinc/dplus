$(function() {
	// BINR FORM INPUTS
	var input_frombin = $('.binr-form').find('input[name=from-bin]');
	var input_tobin   = $('.binr-form').find('input[name=to-bin]');
	var input_qty     = $('.binr-form').find('input[name=qty]');

	/**
	 * The Order of Functions based on Order of Events
	 * NOTE: variables warehouse & validfrombins are generated at run time by the whse-binr template
	 * 1. Select Item (only if theres a list)
	 * 2. Show From bin selection
	 * 3. Choose From Bin
	 * 4. Use bin Qty (if needed)
	 * 5. Show Possible To Bins (if needed) included in _shared-functions.js
	 * 6. Validate Form submit
	 * 7. Helper Functions
	 */

/////////////////////////////////////
// 1. Select Item (only if theres a List)
////////////////////////////////////
	$("body").on("click", ".binr-inventory-result", function(e) {
		var button = $(this);
		var desc = button.data('desc');
		var qty = parseInt(button.data('qty'));
		var title =  desc.toUpperCase() + ' ' + button.data('item') + ' is Not Available';

		if (qty < 1) {
			e.preventDefault();
			swal2.fire({
				type: 'error',
				title: title,
				text: 'The system does not see any quantity at this location'
			});
		}
	});

/////////////////////////////////////
// 2. Show From bin selection
/////////////////////////////////////
	$("body").on("click", ".show-select-bins", function(e) {
		e.preventDefault();
		var button = $(this);
		var bindirection = button.data('direction');
		$('.choose-'+bindirection+'-bins').parent().removeClass('hidden').focus();
	});

/////////////////////////////////////
// 3. Choose From Bin
/////////////////////////////////////
	$("body").on("click", ".choose-from-bins .choose-bin", function(e) {
		e.preventDefault();
		var button = $(this);
		var binID = button.data('binid');
		var qty = button.data('qty');
		var bindirection = button.data('direction');
		$('.binr-form').find('input[name='+bindirection+'-bin]').attr('data-bin', binID);
		$('.binr-form').find('input[name='+bindirection+'-bin]').val(binID);
		//input_qty.val(qty);
		$('.binr-form').find('.qty-available').text(qty);
		button.closest('.list-group').parent().addClass('hidden');
		button.closest('.modal').modal('hide');
	});

/////////////////////////////////////
// 4. Use bin Qty if needed
/////////////////////////////////////
	$("body").on("click", ".use-bin-qty", function(e) {
		e.preventDefault();
		var button = $(this);
		var binID = $('.binr-form').find('input[name=from-bin]').val();
		var binqty = $('.choose-from-bins').find('[data-binid="'+binID+'"]').data('qty');
		$('.binr-form').find('.qty-available').text(binqty);
		input_qty.val(binqty);
	});

/////////////////////////////////////
// 5. Show Possible To Bins (if needed)
//	  included in _shared-functions.js
/////////////////////////////////////
	$("body").on("click", "#choose-to-bins-modal .choose-tobin", function(e) {
		e.preventDefault();
		var button = $(this);
		var binID = button.data('bin');
		input_tobin.val(binID);
		button.closest('.modal').modal('hide');
	});

/////////////////////////////////////
// 6. Validate Form submit
/////////////////////////////////////
	$(".binr-form").validate({
		submitHandler : function(form) {
			var valid_frombin = validate_frombin();
			var valid_qty = validate_qty();
			var valid_tobin = validate_tobin();
			var valid_form = new SwalError(false, '', '', false);

			if (valid_frombin.error) {
				valid_form = valid_frombin;
			} else if (valid_qty.error) {
				valid_form = valid_qty;
			} else if (valid_tobin.error) {
				valid_form = valid_tobin;
			}

			if (valid_form.error) {
				swal2.fire({
					icon: 'error',
					title: valid_form.title,
					text: valid_form.msg,
					html: valid_form.html
				});
			} else {
				form.submit();
			}
		}
	});

/////////////////////////////////////
// Helper Functions
/////////////////////////////////////
	function validate_frombin() {
		var error = false;
		var title = '';
		var msg = '';
		var html = false;
		var lowercase_frombin = input_frombin.val();
		input_frombin.val(lowercase_frombin.toUpperCase());

		if (input_frombin.val() == '') {
			error = true;
			title = 'Error';
			msg = 'Please Fill in the From Bin';
		} else if (validfrombins[input_frombin.val()] === undefined) {
			error = true;
			title = 'Invalid From Bin ID';
			msg = 'Please use a valid From Bin';
		}
		return new SwalError(error, title, msg, html);
	}

	function validate_qty() {
		var error = false;
		var title = '';
		var msg = '';
		var html = false;

		if (input_qty.val() == '') {
			error = true;
			title = 'Error';
			msg = 'Please fill in the Qty';
		}
		return new SwalError(error, title, msg, html);
	}

	function validate_tobin() {
		var error = false;
		var title = '';
		var msg = '';
		var html = false;
		var lowercase_tobin = input_tobin.val();
		input_tobin.val(lowercase_tobin.toUpperCase());

		if (input_tobin.val() == '') {
			error = true;
			title = 'Error';
			msg = 'Please Fill in the To Bin';
		} else if (warehouse.binarrangement == 'list' && warehouse.bins[input_tobin.val()] === undefined) {
			error = true;
			title = 'Invalid Bin ID';
			msg = 'Please use a valid To bin';
		} else if (warehouse.binarrangement == 'range') {
			error = true;

			warehouse.bins.bins.forEach(function(bin) {
				if (input_tobin.val() >= bin.from && input_tobin.val() <= bin.through) {
					error = false;
				}
			});

			if (error) {
				title = 'Invalid To Bin ID';
				msg = 'Your To Bin ID must between these ranges';
				html = "<h4>Valid Bin Ranges<h4>" + create_binrangetable();
			}
		}
		return new SwalError(error, title, msg, html);
	}
});
