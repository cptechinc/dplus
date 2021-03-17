$(function() {
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
