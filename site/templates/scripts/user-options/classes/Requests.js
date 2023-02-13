class UserOptionsRequests {
	static instance = null;

	static getInstance() {
		if (this.instance === null) {
			this.instance = new UserOptionsRequests();
		}
		return this.instance;
	}

	logmUseridExists(userID, callback) {
		var ajax = new AjaxRequest(config.ajax.urls.json + 'msa/validate/logm/id/');

		ajax.setData({userID: userID});
		ajax.request(function(response) {
			callback(response)
		});
	}

	getLogmUser(userID, callback) {
		var ajax = new AjaxRequest(config.ajax.urls.json + 'msa/logm/user/');

		ajax.setData({userID: userID});
		ajax.request(function(response) {
			callback(response)
		});
	}

	userOptionsExists(userID, callback) {
		var ajax = new AjaxRequest(useroptions.config.urls.api.validate);

		ajax.setData({userID: userID});
		ajax.request(function(response) {
			callback(response)
		});
	}

	getUser(userID, callback) {
		var ajax = new AjaxRequest(useroptions.config.urls.api.user);

		ajax.setData({userID: userID});
		ajax.request(function(response) {
			callback(response)
		});
	}
}
