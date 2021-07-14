$(function() {
	$("body").on('change', 'input[type=checkbox][name=checkorder]', function(e) {
		var checkbox = $(this);
		var form = checkbox.closest('form');
		var linenbr = form.find('input[name=linenbr]').val();

		var ajax = new AjaxRequest(form.attr('action'));
		ajax.setMethod('POST');
		ajax.setData(form.serialize());
		ajax.request(function(response) {
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
