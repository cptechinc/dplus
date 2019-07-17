$(function() {
	$("body").on("click", "a", function(e) {
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
				window.location.href = a.attr('href');
			}
		});
	});
    $('.exclude').click(function(e) {
        e.stopPropagation();
    });
});
