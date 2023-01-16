$(function() {
	var ajaxModal = $('#ajax-modal');
	var editModal = $('#edit-account-modal');
	var uri = URI();
	var queryData = uri.query(true);

	if (queryData.hasOwnProperty('focus') && queryData.focus != 'new') {
		$('.customer[data-custid="' + queryData.focus + '"]').addClass('highlight');
		// $('html, body').animate({scrollTop: $('.customer[data-custid="' + queryData.focus + '"]').offset().top,},700,'linear');
	}

/* =============================================================
	Event Functions
============================================================= */
	$("body").on('change', 'input#newcustID', function(e) {
		e.preventDefault();
		var input = $(this);

		console.log(input.val());
		var ajax = new AjaxRequest(config.ajax.urls.json  + 'mar/validate/pty3/custid/');
		ajax.setData({custid: input.val()});
		ajax.request(function(exists) {
			if (exists) {
				swal2.fire({
					title: "Customer " + input.val() + " exists",
					text: 'Edit 3rd Party Freight Accounts?',
					icon: 'question',
					showCancelButton: true,
					confirmButtonText: 'Yes',
					cancelButtonText: 'No',
				}).then((result) => {
					if (result.value) {
						var uri = new URI();
						uri.addQuery('custID', input.val());
						window.location.href = uri.toString();
					} else {
						input.val('');
					}
				});
			}
		});
	});

/* =============================================================
	Lookup Modal Functions
============================================================= */

	// $("body").on('click', '.customer-link', function(e) {
	// 	e.preventDefault();
	// 	var button = $(this);
	// 	var modal  = button.closest('.modal');
	// 	queryData.custID = button.data('custid');
	// 	uri.query(queryData);
	// 	window.location.href = uri.toString();
	// });

/* =============================================================
	New CXM Modal Functions
============================================================= */
	var modal_new = $('#new-cust-modal');

	modal_new.on('show.bs.modal', function (event) {
		var modal = $(this);
		modal.find('input[name=custID]').val('');
		modal.find('form').validate().resetForm();
	});

	modal_new.on('shown.bs.modal', function (event) {
		var modal = $(this);
		modal.find('input[name=custID]').focus();
	});

	var validator = $('#new-cust-form').validate({
		errorClass: "is-invalid",
		validClass: "is-valid",
		errorPlacement: function(error, element) {
			error.insertAfter(element).addClass('invalid-feedback');
		},
		rules: {
			custID: {
				required: true,
				remote: {
					url: config.ajax.urls.json + 'mar/validate/custid/',
					type: "get",
					data: {}
				}
			},
		},
		messages: {
			custID: "Use a valid Cust ID",
		},
		submitHandler: function(form) {
			$('#loading-modal').modal('show');
			var inputCustID = $('input#newcustID');
			var ajax = new AjaxRequest(config.ajax.urls.json  + 'mar/validate/pty3/custid/');
			ajax.setData({custid: inputCustID.val()});
			ajax.request(function(exists) {
				if (exists === false) {
					var modal = $(form).closest('.modal');
					modal.modal('hide');
					$('#loading-modal').modal('hide');

					editModal.find('input[name=custid]').val(inputCustID.val());
					editModal.find('input[name=custid]').change();
					console.log(modal.find('[data-toggle=modal][data-target="#edit-account-modal"]'));
					modal.find('[data-toggle=modal][data-target="#edit-account-modal"]').click();
				}
			});
		}
	});
});