class TrmRequests extends CodeRequestsBase {
	static instance = null;

	static getInstance() {
		if (this.instance === null) {
			this.instance = new TrmRequests();
		}
		return this.instance;
	}

	getCountryCode(code, callback) {
		var ajax = new AjaxRequest(config.ajax.urls.json + 'mar/cocom/code/');

		ajax.setData({code: code});
		ajax.request(function(response) {
			callback(response)
		});
	}

	getTermsGroup(code, callback) {
		var ajax = new AjaxRequest(config.ajax.urls.json + 'mar/trmg/code/');

		ajax.setData({code: code});
		ajax.request(function(response) {
			callback(response)
		});
	}

	getCreditCardCode(code, callback) {
		var ajax = new AjaxRequest(config.ajax.urls.json + 'mar/crcd/code/');

		ajax.setData({code: code});
		ajax.request(function(response) {
			callback(response)
		});
	}

	validateCode(code, callback) {
		var ajax = new AjaxRequest(config.ajax.urls.json + 'mar/validate/trm/code/');

		ajax.setData({code: code});
		ajax.request(function(response) {
			callback(response)
		});
	}

	getCode(code, callback) {
		var ajax = new AjaxRequest(config.ajax.urls.json + 'mar/trm/code/');

		ajax.setData({code: code});
		ajax.request(function(response) {
			callback(response)
		});
	}
}
