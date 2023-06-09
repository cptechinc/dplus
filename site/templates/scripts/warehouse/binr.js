$(function() {
	// BINR FORM INPUTS
	var input_frombin = $('.binr-form').find('input[name=frombin]');
	var input_tobin   = $('.binr-form').find('input[name=tobin]');
	var input_qty	  = $('.binr-form').find('input[name=qty]');

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
		var bindirection = button.data('direction');
		
		$('.binr-form').find('input[name="'+bindirection+'bin"]').attr('data-bin', binID);
		$('.binr-form').find('input[name="'+bindirection+'bin"]').val(binID);
		$('.binr-form').find('input[name="'+bindirection+'bin"]').change();
		
		button.closest('.list-group').parent().addClass('hidden');
		button.closest('.modal').modal('hide');
	});

	$("body").on("change", "input[name=frombin]", function(e) {
		e.preventDefault();
		var uri = URI(config.ajax.urls.json + 'wm/inventory/lotserial-bin/');
		uri.addQuery('itemID', $('.binr-form').find('input[name=itemID]').val());
		uri.addQuery('lotserial', $('.binr-form').find('input[name=lotserial]').val());
		uri.addQuery('binID', $('.binr-form').find('input[name=frombin]').val());

		var ajax = new AjaxRequest(uri.toString());
		ajax.request(function(json) {
			$('.binr-form').find('.qty-available').text(json.qty);
		});
	});

/////////////////////////////////////
// 4. Use bin Qty if needed
/////////////////////////////////////
	$("body").on("click", ".use-bin-qty", function(e) {
		e.preventDefault();
		var button = $(this);
		var binID = $('.binr-form').find('input[name=frombin]').val();
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
	jQuery.validator.addMethod("differentBins", function(value, element) {
		return this.optional(element) || $('input[name=frombin]').val() != $('input[name=tobin]').val();
	}, "Bins must be different");

	$(".binr-form").validate({
		errorClass: "is-invalid",
		validClass: "is-valid",
		errorPlacement: function(error, element) {
			error.addClass('invalid-feedback');

			if (element.closest('.input-parent').length == 0 && element.closest('.input-group-parent').length == 0) {
				error.insertAfter(element);
				return true;
			}
			if (element.closest('.input-group-parent').length) {
				console.log(error);
				error.appendTo(element.closest('.input-group-parent'));
				return true;
			}
			error.appendTo(element.closest('.input-parent'));
		},
		rules: {
			frombin: {
				required: true,
				remote: {
					url: config.ajax.urls.json + 'min/validate/warehouse/bin/',
					type: "get",
					data: {
						jqv: 'true',
						whseID: user.dplus.whseid,
						binID: function() {
							return $('input[name=frombin]').val();
						},
					}
				}
			},
			tobin: {
				required: true,
				differentBins: true,
				remote: {
					url: config.ajax.urls.json + 'min/validate/warehouse/bin/',
					type: "get",
					data: {
						jqv: 'true',
						whseID: user.dplus.whseid,
						binID: function() {
							return $('input[name=tobin]').val();
						},
					}
				}
			},
			qty: {
				required: true,
			},
		},
		submitHandler : function(form) {
			form.submit();
		}
	});

/////////////////////////////////////
// Helper Functions
/////////////////////////////////////

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
});
