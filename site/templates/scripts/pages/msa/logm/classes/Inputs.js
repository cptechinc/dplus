class LogmInputs {
	static instance = null;

	static getInstance() {
		if (this.instance === null) {
			this.instance = new LogmInputs();
		}
		return this.instance;
	}

	constructor() {
		this.id            = 'logm-form',
		this.form          = $('#' + this.id),
		this.loginid       = this.form.find('input[name=id]');
		this.name          = this.form.find('input[name=name]');
		this.printerbrowse = this.form.find('input[name=printerbrowse]');
		this.printerreport = this.form.find('input[name=printerreport]');
		this.groupid       = this.form.find('input[name=groupid]');
		this.roleid        = this.form.find('input[name=roleid]');
		this.whseid        = this.form.find('input[name=whseid]');
	}
}
