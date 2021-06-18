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
