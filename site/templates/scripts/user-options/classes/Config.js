class UserOptionsConfig {
	static instance = null;

	static getInstance() {
		if (this.instance === null) {
			this.instance = new UserOptionsConfig();
		}
		return this.instance;
	}

	constructor() {
		this.fields = useroptions.config.fields
	}
}
