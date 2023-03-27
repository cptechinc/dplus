class PtmRequests extends CodeRequestsBase {
	static instance = null;

	static getInstance() {
		if (this.instance === null) {
			this.instance = new PtmRequests();
		}
		return this.instance;
	}

	validateCode(code, callback) {
		var ajax = new AjaxRequest(config.ajax.urls.json + 'map/validate/ptm/code/');

		ajax.setData({code: code});
		ajax.request(function(response) {
			callback(response)
		});
	}

	getCode(code, callback) {
		var ajax = new AjaxRequest(config.ajax.urls.json + 'map/ptm/code/');

		ajax.setData({code: code});
		ajax.request(function(response) {
			callback(response)
		});
	}
}
