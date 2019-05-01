$(function() {
	// BINR FORM INPUTS
	var input_tobin = $('.select-bin-form').find('input[name=tobin]');

	/**
	 * The Order of Functions based on Order of Events
	 * 1. Show From bin selection found in _shared-functions
	 * 2. Validate Form submit
	 * 3. Helper Functions
	 */


/////////////////////////////////////
// 2. Validate Form submit
/////////////////////////////////////
	$(".select-bin-form").validate({
		submitHandler : function(form) {
			var valid_tobin = validate_tobin();
			var valid_form = new SwalError(false, '', '', false);

			if (valid_tobin.error) {
				valid_form = valid_tobin;
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

	$("body").on("click", "#bins-modal .choose-bin", function(e) {
		e.preventDefault();
		var button = $(this);
		var binID = button.data('bin');
		input_tobin.val(binID);
		button.closest('.modal').modal('hide');
	});

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
			msg = 'Please Fill in the Bin';
		} else if (warehouse.binarrangement == 'list' && whsesession.whse.bins[input_tobin.val()] === undefined) {
			error = true;
			title = 'Invalid Bin ID';
			msg = 'Please Choose a valid bin';
		} else if (warehouse.binarrangement == 'range') {
			error = true;

			whsesession.whse.bins.bins.forEach(function(bin) {
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
