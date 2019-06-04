function SwalError(error, title, msg, html) {
	this.error = error;
	this.title = title;
	this.msg   = msg;
	this.html  = html;
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
				var attribute = array[i].split('=');
				attributes += attribute[0] + '="' + attribute[1] + '" ';
			}
			return attributes.trim();
		} else {
			return '';
		}
	}
}
