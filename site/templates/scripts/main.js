// Well hello there. Looks like we don't have any Javascript.
// Maybe you could help a friend out and put some in here?
// Or at least, when ready, this might be a good place for it.

var nav = '#yt-menu';

$(function() {
	$(window).scroll(function() {
		if ($(this).scrollTop() > 50) {
			$('#back-to-top').fadeIn(); 
		} else {
			$('#back-to-top').fadeOut(); 
		}
	});

	// scroll body to 0px on click
   $('#back-to-top').click(function () {
	   $('#back-to-top').tooltip('hide');
	   $('body,html').animate({ scrollTop: 0 }, 800);
	   return false;
   });

   $("body").on('show', '#yt-menu', function() {
	   alert('');
   });
});

function toggle_nav() {
	$(nav).toggle();
	$(nav).find('input[name=q]').focus();
}