$(function() {
	var form = $('#edit-sales-order-form');
	var input_shiptoid   = form.find('select[name=shiptoid]');
	var input_shiptoname = form.find('input[name=shipto_name]');
	var input_address    = form.find('input[name=shipto_address]');
	var input_address2   = form.find('input[name=shipto_address2]');
	var input_city       = form.find('input[name=shipto_city]');
	var input_state      = form.find('select[name=shipto_state]');
	var input_zip        = form.find('input[name=shipto_zip]');

	input_shiptoid.on('change', function () {
		var select = $(this);
		var shiptoid = select.val();
		var shipto = false;

		if (shiptos[shiptoid]) {
			shipto = shiptos[shiptoid];
			console.log('true');
		} else {
			shipto = new Shipto('', '', '', '', '', '', '');
		}

		input_shiptoname.val(shipto.name);
		input_address.val(shipto.address);
		input_address2.val(shipto.address2);
		input_city.val(shipto.city);
		input_state.val(shipto.state);
		input_zip.val(shipto.zip);
	});

	$("body").on("click", "a:not([href^=#],.sales-order-notes)", function(e) {
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
			}
		});
	});
});
