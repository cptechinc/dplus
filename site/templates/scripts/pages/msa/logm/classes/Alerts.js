class LogmAlerts extends Alerts {
	static instance = null;

	static getInstance() {
		if (this.instance === null) {
			this.instance = new LogmAlerts();
		}
		return this.instance;
	}

	exists(id, callback) {
		var html = 'Edit User <span class="font-monospace font-weight-bold text-underlined">' + id.replace(' ', '&nbsp;') + '</span>?';

		swal2.fire({
			title: 'User Exists',
			icon: 'question',
			html: html,
			showCancelButton: true,
			confirmButtonText: 'Yes'
		}).then(function (result) {
			if (result.value) {
				callback(true);
				return true;
			}
			callback(false);
			return false;
		});
	}
}
