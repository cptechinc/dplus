$(function() {
	// BINR FORM INPUTS
	var form_movecontents = $('.move-contents-form');
	var input_frombin = form_movecontents.find('input[name=from-bin]');
	var input_tobin = form_movecontents.find('input[name=to-bin]');
	var modal_bincontents = $('#bin-contents-modal');
	var modal_selectbin = $('#bins-modal');

	/**
	* The Order of Functions based on Order of Events
	* 1. Show Possible Bins
	* 2. Show Bin Contents
	* 3 . Form, bin validation
	*/

	/////////////////////////////////////
	// 1. Show Possible Bins (if needed)
	/////////////////////////////////////
	modal_selectbin.on('show.bs.modal', function (event) {
		var modal = $(this);
		var button = $(event.relatedTarget); // Button that triggered the modal
		var inputname = button.data('input');
		modal.attr('data-input', inputname);
	});

	$("body").on("click", "#bins-modal .choose-bin", function(e) {
		e.preventDefault();
		var button = $(this);
		var binID = button.data('bin');
		var modal = button.closest('.modal');
		// modal.data('input') isn't updated in the DOM
		var inputname = modal.attr('data-input');
		form_movecontents.find('input[name='+inputname+']').val(binID);
		modal.modal('hide');
	});

	/////////////////////////////////////
	// 2. Show Bin contents (if needed)
	/////////////////////////////////////
	modal_bincontents.on('show.bs.modal', function (event) {
		var button = $(event.relatedTarget); // Button that triggered the modal
		var modal = $(this);
		var loadinto = modal.find('.modal-body');
		var binID = button.closest('.form-group').find('.bin-input').val();
		var pageurl = URI().toString();
		var requesturl = URI(modal.data('redir'));
		requesturl.addQuery('binID', binID);
		var resultsurl = URI(modal.data('resultsurl'));
		resultsurl.addQuery('binID', binID);

		$.get(requesturl.toString(), function() {
			loadinto.loadin(resultsurl.toString(), function() {
				modal.find('.bin-id').text(binID);
			});
		});
	});

	/////////////////////////////////////
	// 3. Form, [To / From Bin] Validation
	/////////////////////////////////////
	form_movecontents.validate({
		submitHandler : function(form) {
			var valid_frombin = validate_frombin();
			var valid_tobin = validate_tobin();
			var valid_form = new SwalError(false, '', '', false);

			if (valid_frombin.error) {
				valid_form = valid_frombin;
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
		} else if (warehouse.binarrangement == 'list' && warehouse.bins[input_frombin.val()] === undefined) {
			error = true;
			title = 'Invalid Bin ID';
			msg = 'Please Choose a valid From bin';
		} else if (warehouse.binarrangement == 'range') {
			error = true;
			title = 'Invalid Bin ID';

			warehouse.bins.forEach(function(bin) {
				if (input_frombin.val() >= bin.from && input_frombin.val() <= bin.through) {
					error = false;
				}
			});

			if (error) {
				title = 'Invalid From Bin ID';
				msg = 'Your From Bin ID must between these ranges';
				html = "<h4>Valid Bin Ranges<h4>" + create_binrangetable();
			}
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
			msg = 'Please Choose a valid To bin';
		} else if (warehouse.binarrangement == 'range') {
			error = true;

			warehouse.bins.forEach(function(bin) {
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
