$(function() {
	var form = $('#edit-quote-form');
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
});
