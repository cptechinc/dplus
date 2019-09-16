$(function() {
	// PHYSICAL COUNT FORM INPUTS
	var form_physcount     = $('#physical-count-form');
	var input_item         = form_physcount.find('input[name=itemID]');
	var input_lotserial    = form_physcount.find('input[name=lotserial]');
	var input_lotserialref = form_physcount.find('input[name=lotserialref]');
	var input_bin          = form_physcount.find('input[name=binID]');
	var input_qty          = form_physcount.find('input[name=qty]');


	if (input_item.val().length) {
		if (input_lotserial.val().length) {
			input_bin.focus();
		} else if (input_item.data('itemtype') == 'S') {
			input_lotserial.focus();
		} else if (input_item.data('itemtype') == 'L') {
			input_lotserial.focus();
		} else {
			input_bin.focus();
		}
	} else {
		input_item.focus();
	}

	form_physcount.validate({
		submitHandler : function(form) {
			var valid_qty     = validate_qty();
			var valid_bin     = validate_bin();
			var valid_form    = new SwalError(false, '', '', false);

			if (valid_bin.error) {
				valid_form = valid_bin;
			} else if (valid_qty.error) {
				valid_form = valid_qty;
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

	function validate_bin() {
		var error = false;
		var title = '';
		var msg = '';
		var html = false;
		var lowercase_bin = input_bin.val();
		input_bin.val(lowercase_bin.toUpperCase());

		if (input_bin.val() == '') {
			error = true;
			title = 'Error';
			msg = 'Please Fill in the Bin';
		} else if (warehouse.binarrangement == 'list' && warehouse.bins[input_bin.val()] === undefined) {
			error = true;
			title = 'Invalid Bin ID';
			msg = 'Please use a valid bin';
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
