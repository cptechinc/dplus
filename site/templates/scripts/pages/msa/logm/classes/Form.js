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
}
