$(function() {
	var ajaxModal = $('#ajax-modal');
	var uri = URI();
	var queryData = uri.query(true);

	if (queryData.hasOwnProperty('focus') && queryData.focus != 'new') {
		$('.customer[data-custid="' + queryData.focus + '"]').addClass('highlight');
		$('html, body').animate({scrollTop: $('.customer[data-custid="' + queryData.focus + '"]').offset().top,},700,'linear');
	}

/* =============================================================
	Lookup Modal Functions
============================================================= */
	ajaxModal.on('show.bs.modal', function (event) {
		var button = $(event.relatedTarget); // Button that triggered the modal
		var modal = $(this);
		modal.attr('data-input', button.data('input'));

		modal.find('.modal-title').text(button.attr('title'));
		modal.resizeModal('xl');
		modal.find('.modal-body').loadin(button.data('lookupurl'), function() {});
	});

	$("body").on('click', '.customer-link', function(e) {
		e.preventDefault();
		var button = $(this);
		var modal  = button.closest('.modal');
		queryData.custID = button.data('custid');
		uri.query(queryData);
		window.location.href = uri.toString();
	});

	$("body").on('submit', '#ajax-modal form', function(e) {
		e.preventDefault();
		var form = $(this);
		var query = form.serialize();
		var action = form.attr('action');
		var search = form.find('input[name=q]').val();
		var url = action + '?' + query;
		form.closest('.modal').find('.modal-title').text('Searching for ' + search);
		form.closest('.modal').find('.modal-body').loadin(url, function() {});
	});

	$("body").on('click', '#ajax-modal .paginator-link', function(e) {
		e.preventDefault();
		var button = $(this);
		var modal  = button.closest('.modal');
		modal.find('.modal-body').load(button.attr('href'));
	});
});