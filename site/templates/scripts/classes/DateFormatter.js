class DateFormatter {

	constructor(date, expectedFormat = '') {
		this.regexer = DateRegexes.getInstance();
		this.momentJsFormats = {
			'mmdd': 'MMDD',
			'mm/dd': 'MM/DD',
			'm/dd': 'M/DD',
			'mmddyyyy': 'MMDDYYYY',
			'mmddyy': 'MMDDYY',
			'mm/dd/yyyy': 'MM/DD/YYYY',
			'yyyymmdd': 'YYYYMMDD',
			'timestamp': 'X'
		},
		this.expectedFormats = {
			'mm/dd': ['mmdd', 'mm/dd', 'm/d'],
			'mm/dd/yyyy': ['mm/dd/yyyy', 'mmddyyyy', 'mmddyy'],
			'yyyymmdd': ['yyyymmdd']
		}
		this.inputDate = date;
		this.date = date;
		this.moment = false;
		this.dateFormat = '';
		this.expectedFormat = expectedFormat;
		this.init();
	}

	init() {
		this.initDateFormat();
		this.initMoment();
	}

	initDateFormat() {
		let patterns = Object.keys(this.regexer.regexes);
		if (this.expectedFormat != '') {
			patterns = this.expectedFormats[this.expectedFormat];
		}
		patterns.forEach(pattern => {
			if (this.dateFormat != '') {
				return;
			}
			if (this.regexer.regexes[pattern].test(this.date)) {
				this.dateFormat = pattern;
			}
		});
		console.log(this.dateFormat);
	}

	initMoment() {
		if (this.dateformat != '') {
			this.moment = moment(this.date, this.momentJsFormats[this.dateFormat]);
		}
	}

	format(format = 'mm/dd/yyyy') {
		if (this.date = '' || this.dateFormat == '') {
			return '';
		}
		if (this.moment.isValid() === false) {
			return '';
		}
		return this.moment.format(this.momentJsFormats[format]);
	}

	isValid() {
		if (this.dateFormat == '') {
			return false;
		}
		return this.moment.isValid();
	}

	updateCentury() {
		if (this.isValid() === false) {
			return false;
		}
		let currentCentury = 2000;
		let minYear = 1970;
		
		if (this.moment.year() >= minYear) {
			return true;
		}
		let remainder = this.moment.year().toString().slice(-2);
		let year = currentCentury + parseInt(remainder);
		this.moment.year(year);
	}
}