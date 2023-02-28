class CodeAlertsBase extends Alerts {
	static instance = null;

	static getInstance() {
		if (this.instance === null) {
			this.instance = new CodeAlertsBase();
		}
		return this.instance;
	}

	unsavedChanges(callback) {
		swal2.fire({
			title: 'Changes have occurred!',
			text: 'Do you want to save?',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: '<i class="fa fa-floppy-o" aria-hidden="true"></i> Yes',
			cancelButtonText: 'No',
			onAfterClose: () => {
				if ($('.is-invalid').length) {
					$('.is-invalid').focus();
					return true;
				}
			}
		}).then((result) => {
			if (result.value) {
				callback(true);
				return true;
			} else if (result.dismiss === Swal.DismissReason.cancel) {
				callback(false);
				return false;
			}
		});
	}

	codeExists(code, callback) {
		var html = 'Edit Code <span class="font-monospace font-weight-bold text-underlined">' + code.replace(' ', '&nbsp;') + '</span>?';

		swal2.fire({
			title: 'Code Exists',
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