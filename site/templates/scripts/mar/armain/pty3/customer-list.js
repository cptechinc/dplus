$(function() {
	var uri = URI();
	var queryData = uri.query(true);

	if (queryData.hasOwnProperty('focus') && queryData.focus != 'new') {
		$('.customer[data-custid="' + queryData.focus + '"]').addClass('highlight');
		$('html, body').animate({scrollTop: $('.customer[data-custid="' + queryData.focus + '"]').offset().top,},700,'linear');
	}
});