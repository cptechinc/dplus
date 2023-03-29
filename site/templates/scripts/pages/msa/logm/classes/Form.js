class LogmForm {
	static instance = null;

	static getInstance() {
		if (this.instance === null) {
			this.instance = new LogmForm();
		}
		return this.instance;
	}

	constructor() {
		this.id = 'logm-form';
		this.form = $('#' + this.id);
		this.inputs = LogmInputs.getInstance();
	}

	findFirstInvalidInput(start, end) {
		let validator = this.form.validate();

		if (validator.errorList.length == 0) {
			return false;
		}
	
		let inputError = false;

		validator.errorList.forEach(error => {
			let input = $(error.element);
			let tabindex = input.attr('tabindex');
			if (inputError !== false) {
				return true;
			}
			console.log(input);
			if (tabindex >= start && tabindex <= end) {
				if (validator.element('#' + input.attr('id'))) {
					return true;
				}
				inputError = input;
			}
		});
		return inputError;
	}

	validateInputsBetweenIndexes(start, end) {
		let validator = this.form.validate();

		for (let i = start; i < end; i++) {
			let input = this.form.find('input[tabindex=' + i + ']');
			if (input.length == 0) {
				continue;
			}

			if (validator.element('#' + input.attr('id')) === false) {
				return false;
			}
		}
		return true;
	}
}
