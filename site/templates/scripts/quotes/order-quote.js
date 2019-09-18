$(function() {
	$.fn.extend({
		http_post: function(callback) {
			var form = $(this);
			var action = form.attr('action');
			var values = form.serialize();
			$.post(action, values, function() {
				callback();
			});
		}
	});

	$('input[type=checkbox]').on('change', function (event) {
		var checkbox = $(this);
		var form = checkbox.closest('form');
		var linenbr = form.find('input[name=linenbr]').val();
		
		form.http_post(function() {

			var title   = "Changes Saved";
			var icon    = "fa fa-floppy-o fa-2x";
			var type    = "success";
			var message = "You added Added Line # " + linenbr;

			if (!checkbox.is(":checked")) {
				type = "warning";
				message = "You removed Added Line # " + linenbr;
			}

			$.notify({
				// options
				title:	 title,
				message: message,
				icon:	 icon
			},{
				// settings
				type: type
			});
		});
	});
});
