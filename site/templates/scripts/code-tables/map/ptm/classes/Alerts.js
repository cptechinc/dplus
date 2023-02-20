class PtmAlerts extends CodeAlertsBase {
	static instance = null;

	static getInstance() {
		if (this.instance === null) {
			this.instance = new PtmAlerts();
		}
		return this.instance;
	}

	orderPercentIsOver100(callback) {
		swal2.fire({
			title: 'Invalid Order Percent',
			icon: 'question',
			text: 'Cannot be more than 100%',
			confirmButtonText: 'Ok'
		}).then(function (result) {
			callback();
			return true;
		});
	}
}