$(function() {
	$("body").on("click", "a:not([href^=#])", function(e) {
		e.preventDefault();
		var a = $(this);

		swal({
			title: 'Order not saved!',
			text: "Are you sure you want to leave this page?",
			type: 'warning',
			showCancelButton: true,
			confirmButtonClass: 'btn btn-success',
			cancelButtonClass: 'btn btn-danger',
			buttonsStyling: false,
			confirmButtonText: 'Yes!'
		}).then(function (result) {
			if (result) {
				var form = $('#edit-sales-order-form');
				var url = URI(form.attr('action'));
				url.addQuery('action', 'unlock-order');
				url.addQuery('ordn', form.find('input[name=ordn]').val());
				$.get(url.toString(), function() {
					window.location.href = a.attr('href');
				});
				console.log(url.toString());
			}
		});
	});
	$('.exclude').click(function(e) {
		e.stopPropagation();
	});
});
