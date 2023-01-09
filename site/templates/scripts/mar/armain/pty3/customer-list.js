$(function() {
	var ajaxModal = $('#ajax-modal');
	var uri = URI();
	var queryData = uri.query(true);

	if (queryData.hasOwnProperty('focus') && queryData.focus != 'new') {
		$('.customer[data-custid="' + queryData.focus + '"]').addClass('highlight');
		// $('html, body').animate({scrollTop: $('.customer[data-custid="' + queryData.focus + '"]').offset().top,},700,'linear');
	}

/* =============================================================
	Lookup Modal Functions
============================================================= */

	$("body").on('click', '.customer-link', function(e) {
		e.preventDefault();
		var button = $(this);
		var modal  = button.closest('.modal');
		queryData.custID = button.data('custid');
		uri.query(queryData);
		window.location.href = uri.toString();
	});
});