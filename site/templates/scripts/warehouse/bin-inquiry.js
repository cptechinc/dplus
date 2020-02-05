$(function() {
	// BINR FORM INPUTS
	var input_bin = $('input[name=binID]');
	var form_physicalcount = $('.physical-count-form');

	/**
	 * The Order of Functions based on Order of Events
	 * NOTE: variables warehouse & validfrombins are generated at run time by the whse-binr template
	 * 1. Select Bin / Validate BIN
	 * 2. Select Item
	 * 3. Physical Count
	 */

/////////////////////////////////////
// 1. Select Bin
////////////////////////////////////
	/**
	 * Validate Bin form
	 */
	$(".select-bin-form").validate({
		submitHandler : function(form) {
			var valid_bin = validate_binID();

			if (valid_bin.error) {
				if (input_bin.val() != '') {
					swal({
						type: 'error',
						title: valid_bin.title,
						text: "Continue bin Inquiry with bin " + input_bin.val() + "?",
						showCancelButton: true,
					}).then(function (input) {
						if (input) {
							form.submit();
						}
					}).catch(swal.noop);;
				} else {
					swal({
						type: 'error',
						title: valid_bin.title,
						text: valid_bin.msg
					});
				}
			} else {
				form.submit();
			}
		}
	});

	/**
	 * Validates Bin ID and returns with an Swal Error object for details
	 * // NOTE THIS IS USED IN STEPS 1, 2
	 * @return SwalError Error
	 */
	function validate_binID() {
		var error = false;
		var title = '';
		var msg = '';
		var html = false;

		var bin_lower = input_bin.val();
		input_bin.val(bin_lower.toUpperCase());

		if (input_bin.val() == '') {
			error = true;
			title = 'Error';
			msg = 'Please Fill in the Bin ID';
		} else if (warehouse.binarrangement == 'list' && warehouse.bins[input_bin.val()] === undefined) {
			error = true;
			title = 'Invalid Bin ID';
			msg = 'Please Choose a valid bin ID';
		} else if (warehouse.binarrangement == 'range') {
			error = true;
			title = 'Invalid Bin ID';
			msg = 'Please Enter a valid bin ID';
			html = "<h4>Valid Bin Ranges<h4>"  + create_binrangetable();

			warehouse.bins.forEach(function(bin) {
				if (input_bin.val() >= bin.from && input_bin.val() <= bin.through) {
					error = false;
				}
			});
		}
		html = false;
		return new SwalError(error, title, msg, html);
	}

	$("body").on("click", "#bins-modal .choose-bin", function(e) {
		e.preventDefault();
		var button = $(this);
		var binID = button.data('bin');
		input_bin.val(binID);
		button.closest('.modal').modal('hide');
	});

	$("body").on("click", ".show-hide-all", function(e) {
		e.preventDefault();
		var button = $(this);

		if (button.attr('showing') == 'true') {
			$('.collapse-lotserial').removeClass('show');
			button.attr('showing', 'false');
		} else {
			$('.collapse-lotserial').addClass('show');
			button.attr('showing', 'true');
		}

	});
});
