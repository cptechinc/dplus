class CodeInputsBase {
	static instance = null;

	static getInstance() {
		if (this.instance === null) {
			this.instance = new CodeInputsBase();
		}
		return this.instance;
	}

	constructor() {
		this.id   = 'code-form';
		this.form = $('#' + this.id);
		this.fields = {
			json: this.form.find('[name=json]')
		};

		let inputs = this;
		let form   = this.form;
		
		codetable.fields.forEach(function(fieldname) {
			inputs.fields[fieldname] = form.find('[name=' + fieldname + ']');
		});
	}
}
