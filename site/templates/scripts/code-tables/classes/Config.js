class CodeConfigBase {
	static instance = null;

	static getInstance() {
		if (this.instance === null) {
			this.instance = new CodeConfigBase();
		}
		return this.instance;
	}

	constructor() {
		this.fields = codetable.config.fields
	}
}
