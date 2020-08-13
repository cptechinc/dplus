$(function() {
	var form_receive = $('#po-item-receive-form');
	var input_qty    = form_receive.find('input[name=qty]');
	var input_itemID    = form_receive.find('input[name=itemID]');
	var input_lotref = form_receive.find('input[name=lotserialref]');

	var form_itemsearch = $('#item-search-form');

	$("body").on("click", "#bins-modal .choose-bin", function(e) {
		e.preventDefault();
		var button = $(this);
		var binID = button.data('bin');
		var input_bin = $('input[name=binID]');
		input_bin.val(binID);
		button.closest('.modal').modal('hide');
	});

	form_receive.validate({
		submitHandler : function(form) {
			var valid_form = new SwalError(false, '', '', false);
			var valid_itemid       = validate_itemid();
			var valid_qty          = validate_qty();
			var valid_lotserialref = validate_lotserialref();
			var valid_bin          = validate_bin(form_receive);
			var valid_qty_exceeds  = validate_qty_exceeds();

			if (valid_itemid.error) {
				valid_form = valid_itemid;
			} else if (valid_qty.error) {
				valid_form = valid_qty;
			} else if (valid_lotserialref.error) {
				valid_form = valid_lotserialref;
			} else if (valid_bin.error) {
				valid_form = valid_bin;
			}

			if (valid_form.error) {
				swal2.fire({
					icon: 'error',
					title: valid_form.title,
					text: valid_form.msg,
					html: valid_form.html
				});
			} else if (valid_qty_exceeds.error) {
				swal2.fire({
					icon: 'warning',
					title: valid_qty_exceeds.title,
					text: valid_qty_exceeds.msg,
					html: valid_qty_exceeds.html
				});
			} else {
				form.submit();
			}
		}
	});

	form_itemsearch.validate({
		errorClass: "is-invalid",
		rules: {
			scan: 'required'
		},
		messages: {
			scan: "Please scan an itemID, Lot/Serial #, etc.",
		},
		submitHandler : function(form) {
			var valid_form = new SwalError(false, '', '', false);

			if (form_itemsearch.data('forcebin') == true) {
				var valid_bin  = validate_bin(form_itemsearch);
			} else {
				var valid_bin = valid_form;
			}

			if (valid_bin.error) {
				valid_form = valid_bin;
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

	function validate_itemid() {
		var error = false;
		var title = '';
		var msg = '';
		var html = false;

		if (form_receive.find('input[name=itemID]').val() == '') {
			error = true;
			title = 'Error';
			msg   = 'Please enter an Item ID';
			form_receive.find('input[name=itemID]').addClass('is-invalid');
		}
		return new SwalError(error, title, msg, html);
	}

	function validate_lotserialref() {
		var error = false;
		var title = '';
		var msg = '';
		var html = false;
		var itemID = input_itemID.val();

		if (input_lotref.val() == '' && items[itemID]['type'] != 'N' && items.length) {
			error = true;
			title = 'Error';
			msg   = 'Please enter a Lot Serial Reference';
			input_lotref.addClass('is-invalid');
		}
		return new SwalError(error, title, msg, html);
	}

	function validate_qty() {
		var error = false;
		var title = '';
		var msg = '';
		var html = false;

		if (input_qty.val() == '0.00' || input_qty.val() == 0.00) {
			error = true;
			title = 'Error';
			msg   = 'Please enter a Quantity greater than 0';
			input_qty.addClass('is-invalid');
		}

		return new SwalError(error, title, msg, html);
	}

	function validate_qty_exceeds() {
		var error = false;
		var title = '';
		var msg = '';
		var html = false;
		var itemID = form_receive.find('input[name=itemID]').val();
		var item = items[itemID];
		var qty = 0;

		if (config_receive['receive_lotserial_as_single']) {
			qty = item.lotserialcount;
			qty += 1;
		} else {
			qty = parseFloat(item.qty_received);
			qty += parseFloat(input_qty.val());
		}

		if (qty > item.qty_ordered && input_qty.attr('data-validated') != 'true') {
			error = true;
			title = 'Warning';
			msg   = 'Quantity Received will exceed Quantity Ordered';

			input_qty.attr('data-validated', 'true');
		}
		return new SwalError(error, title, msg, html);
	}

	function validate_bin(form) {
		var error = false;
		var title = '';
		var msg = '';
		var html = false;
		var input_bin     = form.find('input[name=binID]');
		var lowercase_bin = input_bin.val();
		input_bin.val(lowercase_bin.toUpperCase());

		if (input_bin.val() == '') {
			error = true;
			title = 'Error';
			msg = 'Please Enter a Bin';
			input_bin.addClass('is-invalid');
		} else if (warehouse.binarrangement == 'list' && !warehouse.bins.contains(input_bin.val())) {
			error = true;
			title = 'Invalid Bin ID';
			msg = 'Please use a valid bin';
			input_bin.addClass('is-invalid');
		} else if (warehouse.binarrangement == 'range') {
			error = true;

			warehouse.bins.bins.forEach(function(bin) {
				if (input_bin.val() >= bin.from && input_bin.val() <= bin.through) {
					error = false;
				}
			});

			if (error) {
				title = 'Invalid Bin ID';
				msg = 'Your Bin ID must between these ranges';
				html = "<h4>Valid Bin Ranges<h4>" + create_binrangetable();
				input_bin.addClass('is-invalid');
			}
		}
		return new SwalError(error, title, msg, html);
	}

	function create_binrangetable() {
		var bootstrap = new JsContento();
		var table = bootstrap.open('table', 'class=table table-striped table-condensed');
			whsesession.whse.bins.bins.forEach(function(bin) {
				table += bootstrap.open('tr', '');
					table += bootstrap.openandclose('td', '', bin.from);
					table += bootstrap.openandclose('td', '', bin.through);
				table += bootstrap.close('tr');
			});
		table += bootstrap.close('table');
		return table;
	}
});
