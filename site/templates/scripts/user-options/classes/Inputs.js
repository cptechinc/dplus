class UserOptionsInputs {
	static instance = null;

	static getInstance() {
		if (this.instance === null) {
			this.instance = new UserOptionsInputs();
		}
		return this.instance;
	}

	constructor() {
		this.id   = 'code-form';
		this.form = $('#' + this.id);
		this.fields = {};

		let inputs = this;
		let form   = this.form;
		
		useroptions.fields.forEach(function(fieldname) {
			inputs.fields[fieldname] = form.find('[name=' + fieldname + ']');
		});
	}
}
