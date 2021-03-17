$(function() {
	// PHYSICAL COUNT FORM INPUTS
	var form_physcount     = $('#physical-count-form');
	var input_item         = form_physcount.find('input[name=itemID]');
	var input_lotserial    = form_physcount.find('input[name=lotserial]');
	var input_lotserialref = form_physcount.find('input[name=lotserialref]');
	var input_bin          = form_physcount.find('input[name=binID]');
	var input_qty          = form_physcount.find('input[name=qty]');


	if (input_item.length && input_item.val().length) {
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

	$("body").on("click", "#bins-modal .choose-bin", function(e) {
		e.preventDefault();
		var button = $(this);
		var binID = button.data('bin');
		input_bin.val(binID);
		button.closest('.modal').modal('hide');
	});

/* =============================================================
	Lookup Modal Functions
============================================================= */
	$('#ajax-modal').on('show.bs.modal', function (event) {
		var button = $(event.relatedTarget); // Button that triggered the modal
		var modal = $(this);
		var url = button.data('lookupurl');
		modal.attr('data-input', button.data('input'));

		modal.find('.modal-title').text(button.attr('title'));
		modal.resizeModal('xl');
		modal.find('.modal-body').loadin(url, function() {});
	});

	$("body").on("click", "#ajax-modal .item-link", function(e) {
		e.preventDefault();
		var button = $(this);
		var modal  = button.closest('.modal');
		var itemID = button.data('itemid');
		var input  = $(modal.attr('data-input'));
		input.val(itemID);
		modal.modal('hide');
	});

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
				swal2.fire({
					icon: 'error',
					title: valid_form.title,
					text: valid_form.msg,
					html: valid_form.html
				});
			} else {
				if (input_item.data('itemtype') == 'S' || input_item.data('itemtype') == 'L') {
					if (input_lotserial.val() == '') {
						swal2.fire({
							title: 'Leave lotserial blank?',
							text: "Lot/Serial will be generated if left blank",
							icon: 'warning',
							showCancelButton: true,
							confirmButtonText: 'Continue'
						}).then(function (result) {
							if (result.value) {
								form.submit();
							}
						});
					} else {
						form.submit();
					}
				} else {
					form.submit();
				}
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
