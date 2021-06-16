function VxmRequests() {
	this.validatePrimaryPoOrderCode = function(vendorID, vendoritemID, itemID, callback) {
		var ajax = new AjaxRequest('{{ page.jsonapiURL('map/vxm/primary-ordercode') }}');
		ajax.setData({vendorID: vendorID, vendoritemID: vendoritemID, itemID: itemID});
		ajax.request(function(response) {
			callback(response)
		});
	},
	this.xrefExists = function(vendorID, vendoritemID, itemID, callback) {
		var ajax = new AjaxRequest('{{ page.jsonapiURL('map/validate/vxm') }}');
		ajax.setData({vendorID: vendorID, vendoritemID: vendoritemID, itemID: itemID});
		ajax.request(function(response) {
			callback(response)
		});
	},
	this.canUpdateItmCost = function(vendorID, vendoritemID, itemID, ordercode, callback) {
		var ajax = new AjaxRequest('{{ page.jsonapiURL('map/validate/vxm/update-itm-cost') }}');
		ajax.setData({vendorID: vendorID, vendoritemID: vendoritemID, itemID: itemID, ordercode: ordercode});
		ajax.request(function(response) {
			callback(response)
		});
	},
	this.getItm = function(itemID, itmfields, callback) {
		var fields = Array.isArray(itemfields) ? itemfields.join(',') : itemfields;
		var ajax = new AjaxRequest('{{ page.jsonapiURL('inv/item') }}');

		ajax.setData({itemID: itemID, fields: fields});
		ajax.request(function(response) {
			callback(response)
		});
	}
}

function VxmAlerts() {
	this.xrefExists = function(vendorID, vendoritemID, itemID) {
		swal2.fire({
			title: "Vendor Item " + vendoritemID + " exists",
			text: 'Would you like to go edit this item?',
			icon: 'error',
			showCancelButton: true,
			confirmButtonText: 'Yes',
			cancelButtonText: 'No',
		}).then((result) => {
			if (result.value) {
				var uri = new URI();
				uri.removeQuery('vendoritemID');
				uri.setQuery('vendorID', vendorID);
				uri.setQuery('vendoritemID', vendoritemID);
				uri.setQuery('itemID', itemID);
				window.location.href = uri.toString();
			}
		});
	},
	this.changePrimaryPoOrderCode = function(itemID) {
		swal2.fire({
			title: "Overwrite Primary PO Ordercode?",
			text: itemID + " already has a primary";,
			icon: 'warning',
			showCancelButton: true,
			buttonsStyling: false,
			confirmButtonText: 'Yes!'
		}).then(function (result) {
			if (result.value) {
				// TODO change input validated in callback function
				// var input_validatedpoordercode = form_vxm.find('input[name=po_ordercode_validated]');
				// input_validatedpoordercode.val('true');
				callback(true);
			} else {
				callback(false);
			}
		});
	},
	this.unsavedChanges = function(callback) {
		swal2.fire({
			title: 'Changes have occurred!',
			text: 'Do you want to save?',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: '<i class="fa fa-floppy-o" aria-hidden="true"></i> Yes',
			cancelButtonText: 'No',
		}).then((result) => { // Return True for save, false for dismiss
			// if (result.value) {
			// 	form_vxm.find('button[type=submit]').click();
			// } else if (result.dismiss === Swal.DismissReason.cancel) {
			// 	// Send HTTP GET Request to remove Record Lock
			// 	$.get('{{ page.url }}', function() {
			// 		window.location.href = href;
			// 	});
			// }
			if (result.value) {
				callback(true)
			} else if (result.dismiss === Swal.DismissReason.cancel) {
				callback(false);
			}
		});
	},
	this.confirmCost = function(type, costbase, callback) {
		var msg = 'This is the <b>' + type + '</b> Vendor for this item and ' +
		'the Standard Cost Base code is <b>'+costbase+'</b>, '+
		'do you want to update the <b>Standard Cost</b> on the Item Master?';

		swal2.fire({
			title: 'Confirm Update',
			html: msg,
			icon: 'question',
			showCancelButton: true,
			confirmButtonText: 'Yes',
			cancelButtonText: 'No',
		}).then((result) => {
			callback(result.value);
		});
	}
}
