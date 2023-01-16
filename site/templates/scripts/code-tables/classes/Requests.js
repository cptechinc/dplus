class CodeRequestsBase {
	static instance = null;

	static getInstance() {
		if (this.instance === null) {
			this.instance = new CodeRequestsBase();
		}
		return this.instance;
	}

	validateCode(code, callback) {
		var ajax = new AjaxRequest(codetable.config.urls.api.validate);

		ajax.setData({code: code});
		ajax.request(function(response) {
			callback(response)
		});
	}

	codeExists(code, callback) {
		var ajax = new AjaxRequest(codetable.config.urls.api.validate);

		ajax.setData({code: code});
		ajax.request(function(response) {
			callback(response)
		});
	}

	getCode(code, callback) {
		var ajax = new AjaxRequest(codetable.config.urls.api.code);

		ajax.setData({code: code});
		ajax.request(function(response) {
			callback(response)
		});
	}

	lockCode(code, callback) {
		var ajax = new AjaxRequest(config.ajax.urls.locker.lock);

		ajax.setData({function: codetable.code, key: code});
		ajax.request(function(isLocked) {
			callback(isLocked)
		});
	}

	unlockCode(code, callback) {
		var ajax = new AjaxRequest(config.ajax.urls.locker.delete);

		ajax.setData({function: codetable.code, key: code});
		ajax.request(function(deleted) {
			callback(deleted)
		});
	}
}
