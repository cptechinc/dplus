class UserOptionsAlerts extends Alerts {
	static instance = null;

	static getInstance() {
		if (this.instance === null) {
			this.instance = new UserOptionsAlerts();
		}
		return this.instance;
	}

	userExists(code, callback) {
		var html = 'Edit User <span class="font-monospace font-weight-bold text-underlined">' + code.replace(' ', '&nbsp;') + '</span>?';

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