class DateRegexes {
	static instance = null;

	static getInstance() {
		if (this.instance === null) {
			this.instance = new DateRegexes();
		}
		return this.instance;
	}

	constructor() {
		this.patterns = {
			'mm/dd/yyyy': '(0[1-9]|1[012])\/(0[1-9]|[12][0-9]|3[01])\/(\\d{4})',
			'mmddyyyy': '(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])(\\d{4})',
			'mmddyy': '(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])(\\d{2})',
			'mmdd': '(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])',
			'mm/dd': '(0[1-9]|1[012])\/(0[1-9]|[12][0-9]|3[01])',
			'm/dd': '(0?[1-9]|1[012])\/(0[1-9]|[12][0-9]|3[01])', // USE mm/dd first
			'yyyymmdd': '(\\d{4})(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])'
		};
		this.regexes = {
			'mm/dd/yyyy': new RegExp(this.patterns['mm/dd/yyyy']),
			'mmddyyyy': new RegExp(this.patterns['mmddyyyy']),
			'mmddyy': new RegExp(this.patterns['mmddyy']),
			'mmdd': new RegExp(this.patterns['mmdd']),
			'mm/dd': new RegExp(this.patterns['mm/dd']),
			'm/dd': new RegExp(this.patterns['m/dd']),
			'yyyymmdd': new RegExp(this.patterns['yyyymmdd']),
		};
	}
}