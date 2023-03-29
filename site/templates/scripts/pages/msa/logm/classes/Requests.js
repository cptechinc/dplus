class LogmRequests {
	static instance = null;

	static getInstance() {
		if (this.instance === null) {
			this.instance = new LogmRequests();
		}
		return this.instance;
	}

	validateId(id) {
		let ajax = new AjaxRequest(config.ajax.urls.json + 'msa/logm/id/');

		ajax.setData({id: id});
		ajax.request(function(response) {
			callback(response)
		});
	}

	getWarehouse(id, callback) {
		let ajax = new AjaxRequest(config.ajax.urls.json + 'min/iwhm/code/');

		ajax.setData({id: id});
		ajax.request(function(response) {
			callback(response)
		});
	}

	getPrinter(id, callback) {
		let ajax = new AjaxRequest(config.ajax.urls.json + 'msa/printer/');

		ajax.setData({id: id});
		ajax.request(function(response) {
			callback(response)
		});
	}

	getLoginRole(id, callback) {
		let ajax = new AjaxRequest(config.ajax.urls.json + 'msa/lrole/role/');

		ajax.setData({id: id});
		ajax.request(function(response) {
			callback(response)
		});
	}

	getLoginGroup(code, callback) {
		let ajax = new AjaxRequest(config.ajax.urls.json + 'msa/lgrp/code/');

		ajax.setData({code: code});
		ajax.request(function(response) {
			callback(response)
		});
	}
}
