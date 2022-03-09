function SwalError(error, title, msg, html) {
	this.error = error;
	this.title = title;
	this.msg   = msg;
	this.html  = html;
}

function Shipto(id, name, address, address2, city, state, zip) {
	this.id       = id;
	this.name     = name;
	this.address  = address;
	this.address2 = address2;
	this.city     = city;
	this.state    = state;
	this.zip      = zip;
}

function JsContento() {
	this.open = function(element, attr) {
		var attributes = this.parseattributes(attr);
		return '<'+element+' '+attributes+'>';
	},
	this.close = function (element) {
		return '</'+element+'>';
	},
	this.openandclose = function(element, attr, content) {
		return this.open(element, attr) + content + this.close(element);
	},
	this.parseattributes = function(attr) {
		if (attr.trim() != '') {
			var array = attr.split('|');
			var attributes = '';

			for (var i = 0; i < array.length; i++) {
				var index = array[i].indexOf('=');

				var attribute = array[i].split('=');

				if (attribute.length > 2) {
					attribute = [array[i].slice(0, index),array[i].slice(index + 1)];
				}
				attributes += attribute[0] + '="' + attribute[1] + '" ';
			}
			return attributes.trim();
		} else {
			return '';
		}
	}
}

function AjaxRequest(url) {
	this.url = url;
	this.method = 'GET';
	this.data = {};
	this.setData = function(data) {
		this.data = data;
	},
	this.setMethod = function(method) {
		this.method = method;
	},
	this.request = function(callback) {
		$.ajax({
			url: this.url,
			method: this.method,
			beforeSend: function(xhr) {},
			data: this.data,
			success: function(json) {
				callback(json);
			},
			error: function(xhr){
			},
		});
	}
}

class Alerts {
	static instance = null;

	static getInstance() {
		if (this.instance === null) {
			this.instance = new Alerts();
		}
		return this.instance;
	}

	unsavedChanges(callback) {
		swal2.fire({
			title: 'Changes have occurred!',
			text: 'Do you want to save?',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: '<i class="fa fa-floppy-o" aria-hidden="true"></i> Yes',
			cancelButtonText: 'No',
		}).then((result) => {
			if (result.value) {
				callback(true);
				return true;
			} else if (result.dismiss === Swal.DismissReason.cancel) {
				callback(false);
				return false;
			}
		});
	}

	delete(callback) {
		swal2.fire({
			title: 'Confirm Deletion',
			text: "Are you sure you want to delete?",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonClass: 'btn btn-success',
			cancelButtonClass: 'btn btn-danger'
		}).then((result) => {
			if (result.value) {
				callback(true);
				return true;
			} else if (result.dismiss === Swal.DismissReason.cancel) {
				callback(false);
				return false;
			}
		});
	}
}

class ImageModal {
	static instance = null;

	static getInstance() {
		if (this.instance === null) {
			this.instance = new ImageModal();
		}
		return this.instance;
	}

	constructor() {
		this.id = 'image-modal';
		this.modal = $('#' + this.id);
	}

	updateTitle(type, id) {
		var modalTitle = this.modal.find('.modal-title');
		modalTitle.find('.type').text(type);
		modalTitle.find('.id').text(id);
	}

	updateImage(folder, file) {
		var modal = this;

		this.requestImageCopy(folder, file, function(success) {
			if (success) {
				var img = modal.modal.find('img');
				img.attr('src', config.urls.docvwr + file);
			}
		});
	}

	requestImageCopy(folder, file, callback) {
		var ajax = new AjaxRequest(api.urls.mdm.docvwr.copy);
		ajax.setData({folder: folder, file:file});
		ajax.request(function(success) {
			callback(success);
		});
	}
}
