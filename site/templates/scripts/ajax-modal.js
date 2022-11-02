$(function() {
	var ajaxModal = $('#ajax-modal');

	ajaxModal.on('show.bs.modal', function (event) {
		var button = $(event.relatedTarget); // Button that triggered the modal
		var modal = $(this);
		modal.attr('data-input', button.data('input'));

		modal.find('.modal-title').text(button.attr('title'));
		modal.resizeModal('xl');
		modal.find('.modal-body').loadin(button.data('lookupurl'), function() {});
	});

	$("body").on('click', '.code-link', function(e) {
		e.preventDefault();
		var button = $(this);
		var modal  = button.closest('.modal');
		var input  = $(modal.attr('data-input'));
		input.val(button.data('code'));
		if (input.data('jqv')) {
			input.change();
		}
		modal.modal('hide');
	});

	$("body").on('click', '.customer-link', function(e) {
		e.preventDefault();
		var button = $(this);
		var modal  = button.closest('.modal');
		var input  = $(modal.attr('data-input'));
		input.val(button.data('custid'));
		if (input.data('jqv')) {
			input.change();
		}
		modal.modal('hide');
	});

	$("body").on('click', '.item-link', function(e) {
		e.preventDefault();
		var button = $(this);
		var itemID = button.data('itemid');
		var modal = button.closest('.modal');
		var input = $(modal.attr('data-input'));
		input.val(itemID);
		if (input.data('jqv')) {
			input.change();
		}
		modal.modal('hide');
	});

	$("body").on('submit', '#ajax-modal form', function(e) {
		e.preventDefault();
		var form = $(this);
		var query = form.serialize();
		var action = form.attr('action');
		var search = form.find('input[name=q]').val();
		var url = action + '?' + query;
		var modal = form.closest('.modal');
		
		modal.find('.modal-body').loadin(url, function(ajaxResponse) {
			if (ajaxResponse.headers.hasOwnProperty('page-headline')) {
				modal.find('.modal-title').text('Search ' + ajaxResponse.headers['page-headline']);
			}
		});
	});

	$("body").on('click', '#ajax-modal .paginator-link', function(e) {
		e.preventDefault();
		var button = $(this);
		var modal  = button.closest('.modal');
		modal.find('.modal-body').load(button.attr('href'));
	});

	$("body").on('click', '#ajax-modal a.clear-search', function(e) {
		e.preventDefault();
		var button = $(this);
		var modal  = button.closest('.modal');
		modal.find('.modal-body').loadin(button.attr('href'), function(ajaxResponse) {
			if (ajaxResponse.headers.hasOwnProperty('page-headline')) {
				modal.find('.modal-title').text('Search ' + ajaxResponse.headers['page-headline']);
			}
		});
	});

	$("body").on('click', '#ajax-modal a.sort', function(e) {
		e.preventDefault();
		var button = $(this);
		var modal  = button.closest('.modal');
		modal.find('.modal-body').load(button.attr('href'));
	});
});