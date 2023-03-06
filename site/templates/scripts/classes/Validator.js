class Validator {
	static instance = null;

	static getInstance() {
		if (this.instance === null) {
			this.instance = new Validator();
		}
		return this.instance;
	}

	dateMMDDSlash(date) {
		return DateRegexes.getInstance().regexes['mm/dd'].test(date);
	}

	dateMMYYSlash(date) {
		return DateRegexes.getInstance().regexes['mm/dd'].test(date);
	}

	dateMMDDYYYYSlash(dateString) {
		let date = new DateFormatter(dateString, 'mm/dd/yyyy');
		return date.isValid();
	}

	dateIsInFuture(dateString, dateFormat = 'mm/dd/yyyy') {
		let date = new DateFormatter(dateString, dateFormat);

		if (date.isValid() === false) {
			return false;
		}
		let today = new DateFormatter();
		return parseInt(date.format('timestamp')) > parseInt(today.format('timestamp'));
	}
}
