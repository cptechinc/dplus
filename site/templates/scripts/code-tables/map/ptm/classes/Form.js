class PtmForm extends CodeFormBase {
	static instance = null;

	static getInstance() {
		if (this.instance === null) {
			this.instance = new PtmForm();
		}
		return this.instance;
	}

	constructor() {
		super();

		this.stdInputs = {
			'lastindex': 1,
			'splits': []
		};
	}

	/**
	 * Return if the Terms Method is End of Month
	 * @returns bool
	 */
	isMethodEom() {
		return this.inputs.fields.method.val() == codetable.config.methods.eom.value;
	}

	/**
	 * Return if the Terms Method is Standard
	 * @returns bool
	 */
	isMethodStd() {
		return this.inputs.fields.method.val() == codetable.config.methods.std.value;
	}

	/**
	 * Return if Input is EOM thru day
	 * @param {Object} input 
	 * @returns bool
	 */
	isInputEomThruDay(input) {
		return input.hasClass('eom_thru_day');
	}

/* =============================================================
	Method STD
============================================================= */
	/**
	 * Return a total of all input.order_percent values 
	 * @returns {float}
	 */
	sumUpStdOrderPercents() {
		let form    = this;
		let formStd = form.form.find('#std-splits');
		let total    = 0.00;
		
		formStd.find('input.std_order_percent').each(function() {
			let input = $(this);
			let percent = form.floatVal(input.val());
			console.log(percent);
			percent = percent.toFixed(form.config.fields.std_order_percent.precision);
			total = parseFloat(total) + parseFloat(percent);
		});
		return total.toFixed(form.config.fields.std_order_percent.precision);
	}

/* =============================================================
	Supplemental
============================================================= */
	/**
	 * Return Float Number
	 * @param {*} value 
	 * @returns {number}
	 */
	floatVal(val) {
		if (isNaN(val) || val == '') {
			return 0;
		}
		return parseFloat(val);
	}
}
