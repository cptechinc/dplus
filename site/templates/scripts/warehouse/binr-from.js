$(function() {
	// BINR FORM INPUTS
	var input_frombin = $('.select-bin-form').find('input[name=frombin]');

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
			var valid_frombin = validate_frombin();
			var valid_form = new SwalError(false, '', '', false);

			if (valid_frombin.error) {
				valid_form = valid_frombin;
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
		input_frombin.val(binID);
		button.closest('.modal').modal('hide');
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
			msg = 'Please Fill in the Bin';
		} else if (warehouse.binarrangement == 'list' && whsesession.whse.bins[input_frombin.val()] === undefined) {
			error = true;
			title = 'Invalid Bin ID';
			msg = 'Please Choose a valid bin';
		} else if (warehouse.binarrangement == 'range') {
			error = true;

			whsesession.whse.bins.bins.forEach(function(bin) {
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
});
