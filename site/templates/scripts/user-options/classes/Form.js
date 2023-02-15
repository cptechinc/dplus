class UserOptionsForm {
	static instance = null;

	static getInstance() {
		if (this.instance === null) {
			this.instance = new UserOptionsForm();
		}
		return this.instance;
	}

	constructor() {
		this.id = 'user-form';
		this.form = $('#' + this.id);
		this.inputs = UserOptionsInputs.getInstance();
		this.config = UserOptionsConfig.getInstance();
	}

	serializeForm(serialize = true) {
		if (serialize == false) {
			this.form.attr('data-serialized', '');
			return true;
		}
		this.form.attr('data-serialized', this.form.serialize());
	}
}
