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
	
	/**
	 * Return if Input is EOM thru day
	 * @param {Object} input 
	 * @returns bool
	 */
	isInputStdOrderPercent(input) {
		return input.hasClass('std_order_percent');
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
			percent = percent.toFixed(form.config.fields.std_order_percent.precision);
			total = parseFloat(total) + parseFloat(percent);
		});
		return total.toFixed(form.config.fields.std_order_percent.precision);
	}

	/**
	 * Return base names for STD slit input
	 * @returns {array}
	 */
	getStdSplitInputBaseNames() {
		let inputClasses = [
			'std_order_percent',
			'std_disc_percent', 'std_disc_days', 'std_disc_day', 'std_disc_date',
			'std_due_days', 'std_due_day', 'std_plus_months', 'std_due_date', 'std_plus_years'
		];
		return inputClasses;
	}

	/**
	 * Return Object of STD discount fields excluding input.std_disc_percent
	 * @param	{HTMLElement} parent
	 * @returns {Object}
	 */
	getStdDiscFieldsByStdDiscGroup(parent) {
		let inputs = {
			'std_disc_days': parent.find('input.std_disc_days'),
			'std_disc_day': parent.find('input.std_disc_day'),
			'std_disc_date': parent.find('input.std_disc_date')
		};
		return inputs;
	}

	/**
	 * Return Object of STD Due Primary fields
	 * @param   {HTMLElement} parent 
	 * @returns {Object}
	 */
	getStdDuePrimaryFieldsByStdDueGroup(parent) {
		let inputs = {
			'std_due_days': parent.find('input.std_due_days'),
			'std_due_day': parent.find('input.std_due_day'),
			'std_due_date': parent.find('input.std_due_date')
		};
		return inputs;
	}

	/**
	 * Return Object of All Inputs for STD method
	 * @returns {Object}
	 */
	getAllStdInputs(refresh = false) {
		if (this.stdInputs.splits.length > 0 && refresh === false) {
			return this.stdInputs;
		}

		let stdInputs = {
			'lastindex': 1,
			'splits': []
		};
		let inputClasses = this.getStdSplitInputBaseNames();
		
		let formStd = this.form.find('#std-splits');
		for (var i = 1; i <= codetable.config.methods.std.splitCount; i++) {
			let inputs = [];
			
			inputClasses.forEach(name => {
				let input = formStd.find('input[name=' + name + i + ']'); 
				inputs[name] = input;
			});
			if (inputs.std_order_percent.val() != '') {
				stdInputs.lastindex = i;
			}
			stdInputs.splits[i] = {
				'inputs': inputs
			}
		}
		this.stdInputs = stdInputs;
		return this.stdInputs;
	}

	/**
	 * Enable / Disable all split inputs based off input.order_percent
	 * @param	{number} index
	 * @returns {bool}
	 */
	clearStdSplitInputs(index) {
		if (this.isMethodStd() === false || index > codetable.config.methods.std.splitCount) {
			return false;
		}
		let split = this.form.find('.std-split[data-index='+ index +']');
		let validator = this.form.validate();
		
		split.find('input').each(function() {
			let input = $(this);
			input.val('');
			validator.element('#' + input.attr('id'));
		});
	}

	/**
	 * Handle input.std_order_percent clear
	 * @param {HTMLInputElement} input 
	 * @returns 
	 */
	handleStdOrderPercentClear(input) {
		if (this.isMethodStd() === false || this.isInputStdOrderPercent(input) === false) {
			return false;
		}
		this.addOrderPercentToLast(input);
		this.shiftStdSplitValuesUp(input);
		
		let index = parseInt(input.closest('.std-split').data('index'));
		let allInputs = this.getAllStdInputs(true);
	}

	/**
	 * Disable All Inputs for STD Split
	 * @param {Number} index 
	 */
	disableStdSplit(index) {
		let allInputs = this.getAllStdInputs();
		let splitCurr = allInputs.splits[index];
		let keys = this.getStdSplitInputBaseNames();
		let form = this;
		keys.forEach(name => {
			let inputCurr = splitCurr.inputs[name];
			form.setReadonly(inputCurr, true);
			form.disableTabindex(inputCurr);
		});
	}

	/**
	 * Enable STD Split's Basic fields
	 * @param {Number} index 
	 */
	enableStdSplit(index) {
		let allInputs = this.getAllStdInputs();
		let splitCurr = allInputs.splits[index];
		let form = this;

		let inputs = {
			std_disc_percent: splitCurr.inputs.std_disc_percent,
			std_due_days: splitCurr.inputs.std_due_days,
			std_due_day: splitCurr.inputs.std_due_day,
			std_due_date: splitCurr.inputs.std_due_date,
		}
		Object.keys(inputs).forEach(name => {
			let input = inputs[name];
			form.setReadonly(input, false);
			form.enableTabindex(input);
		});
	}

	/**
	 * Move the values of split inputs one index 1 up
	 * @param	{HTMLInputElement} input 
	 * @returns {Object}
	 */
	shiftStdSplitValuesUp(input) {
		if (this.isMethodStd() === false || this.isInputStdOrderPercent(input) === false) {
			return false;
		}
		let index = parseInt(input.closest('.std-split').data('index'));
		let allInputs = this.getAllStdInputs();

		for (let i = index; i <= codetable.config.methods.std.splitCount; i++) {
			let splitCurr = allInputs.splits[i];
			let splitNext = allInputs.splits[i + 1];
			let keys = this.getStdSplitInputBaseNames();
			let form = this;
			
			if (splitNext !== undefined) {
				keys.forEach(name => {
					let inputCurr = splitCurr.inputs[name];
					let inputNext = splitNext.inputs[name];
					
					inputCurr.val(inputNext.val());

					if (name == 'std_order_percent') {
						inputCurr.attr('data-lastvalue', inputCurr.val());
					}
	
					if (inputNext.attr('readonly') !== undefined) {
						form.setReadonly(inputCurr, true);
						form.disableTabindex(inputCurr);
					}
	
					if (inputNext.attr('readonly') === undefined) {
						form.setReadonly(inputCurr, false);
						form.enableTabindex(inputCurr);
					}
				});
			}
		}
	}
	
	/**
	 * Add the value of this Order Percent to the previous split's order percent
	 * @param {HTMLInputElement} input 
	 * @returns 
	 */
	addOrderPercentToLast(input) {
		if (this.isMethodStd() === false || this.isInputStdOrderPercent(input) === false) {
			return false;
		}
		let index = parseInt(input.closest('.std-split').data('index'));
		if (index == 1) {
			return false;
		}
		let thisOrderPercentLast  = input.attr('data-lastvalue');
		let allInputs = this.getAllStdInputs();

		let lastInputOrderPercent = allInputs.splits[index - 1].inputs.std_order_percent;
		let percent = parseFloat(lastInputOrderPercent.val()) + parseFloat(thisOrderPercentLast);
		let value = percent.toFixed(this.config.fields.std_order_percent.precision);
		lastInputOrderPercent.val(value);
		lastInputOrderPercent.attr('data-lastvalue', value);
	}

	/**
	 * Setup values for Next STD Split
	 * @param {HTMLInputElement} input 
	 * @returns 
	 */
	setupNextStdSplit(input) {
		if (this.isMethodStd() === false || this.isInputStdOrderPercent(input) === false) {
			return false;
		}
		let thisSplit = input.closest('.std-split');
		let index = parseFloat(thisSplit.data('index'));

		if (index >= codetable.config.methods.std.splitCount) {
			return false;
		}

		let totalPercent = this.sumUpStdOrderPercents();
		let nextIndex = index + 1;
		let nextSplit = $('.std-split[data-index='+ (nextIndex) +']');

		if (totalPercent == 100) {
			return true;
		}

		if (totalPercent <= 100 && input.val() != '') {
			let nextPercent = this.floatVal(100 - totalPercent);
			let nextInputPercent = nextSplit.find('input.std_order_percent');
			if (nextInputPercent.val()) {
				nextPercent += this.floatVal(nextInputPercent.val());
			}
			nextInputPercent.val(nextPercent.toFixed(this.config.fields.std_order_percent.precision));
			nextInputPercent.attr('data-lastvalue', nextInputPercent.val());
		}
	}

	/**
	 * Enable / Disable STD Split Inputs
	 * @param {number} index 
	 * @returns 
	 */
	enableDisableStdSplitInputs(index) {
		if (this.isMethodStd() === false || index > codetable.config.methods.std.splitCount) {
			return false;
		}

		if (this.sumUpStdOrderPercents() != 100) {
			return false;
		}
		let stdSplit = this.form.find('.std-split[data-index='+ index +']');
		let stdDiscInputs = this.getStdDiscFieldsByStdDiscGroup(stdSplit);
		let stdDueInputs = this.getStdDuePrimaryFieldsByStdDueGroup(stdSplit);
		let form = this;

		this.enableStdSplit(index);

		Object.keys(stdDiscInputs).forEach(name => {
			if (stdDiscInputs[name].val() != '') {
				form.enableDisableStdDiscFieldsFromInputClass(stdDiscInputs[name], name);
			}
		});

		if (stdDueInputs['std_due_days'].val() != '') {
			this.enableDisableStdPrimaryDueFieldsFromDueDays(stdDueInputs['std_due_days']);
		}

		if (stdDueInputs['std_due_day'].val() != '') {
			this.enableDisableStdPrimaryDueFieldsFromDueDay(stdDueInputs['std_due_day']);
			this.enableDisableStdDependentFieldsFromDueDay(stdDueInputs['std_due_day']);
			
		}

		if (stdDueInputs['std_due_date'].val() != '') {
			this.enableDisableStdPrimaryDueFieldsFromDueDate(stdDueInputs['std_due_date']);
		}
	}

	/**
	 * Enable / Disable Next STD Split
	 * @param {number} index 
	 * @returns 
	 */
	enableDisableNextStdSplit(index) {
		let nextIndex = index + 1;
		if (nextIndex >= codetable.config.methods.std.splitCount) {
			return false;
		}
		let allInputs = this.getAllStdInputs();
		let nextSplit = allInputs.splits[nextIndex];

		if (nextSplit.inputs.std_order_percent.val() != '') {
			this.setReadonly(nextSplit.inputs.std_order_percent, false);
			this.enableTabindex(nextSplit.inputs.std_order_percent);
			this.enableDisableStdSplitInputs(nextIndex);
			return true;
		}
		this.setReadonly(nextSplit.inputs.std_order_percent, true);
		this.disableTabindex(nextSplit.inputs.std_order_percent);
		this.enableDisableStdSplitInputs(nextIndex);
	}

	/**
	 * Enable Discount / Day Month fields based off Discount Percent Value
	 * @param	{Object} input 
	 * @returns 
	 */
	enableDisableStdDiscFieldsFromDiscPercent(input) {
		if (this.isMethodStd() === false || input.hasClass('std_disc_percent') === false) {
			return false;
		}
		let parent	= input.closest('.std-discount');
		let percent = this.floatVal(input.val());

		let inputs = this.getStdDiscFieldsByStdDiscGroup(parent);


		if (percent == 0) {
			this.enableDisableInputs(Object.values(inputs), false);
			return true;
		}

		this.enableDisableInputs(Object.values(inputs), false);
		let enableAll = true;

		Object.keys(inputs).forEach(name => {
			if (inputs[name].val() != 0) {
				this.setReadonly(inputs[name], false);
				this.enableTabindex(inputs[name]);
				enableAll = false;
			}
		});
		if (enableAll === false) {
			return true;
		}
		this.enableDisableInputs(Object.values(inputs), true);
	}

	/**
	 * Enable / Disable Discount inputs that don't have x name
	 * @param {HTMLElement} input 
	 * @param {string}		name 
	 * @returns 
	 */
	enableDisableStdDiscFieldsFromInputClass(input, name) {
		let parent = input.closest('.std-discount');

		if (this.isMethodStd() === false || parent.length == 0) {
			return false;
		}

		if (input.val() == '0' || input.val() == '') {
			this.enableDisableStdDiscFieldsFromDiscPercent(parent.find('.std_disc_percent'));
			return true;
		}

		let inputs = this.getStdDiscFieldsByStdDiscGroup(parent);
		delete inputs[name];
		this.enableDisableInputs(Object.values(inputs), false);
	}

	/**
	 * Enable / Disable Other STD discount fields that arent input.std_disc_days
	 * @param {HTMLElement} input 
	 * @returns 
	 */
	enableDisableStdDiscFieldsFromDays(input) {
		if (this.isMethodStd() === false || input.hasClass('std_disc_days') === false) {
			return false;
		}
		this.enableDisableStdDiscFieldsFromInputClass(input, 'std_disc_days');
	}

	/**
	 * Enable / Disable Other STD discount fields that arent input.std_disc_day
	 * @param {HTMLElement} input 
	 * @returns 
	 */
	enableDisableStdDiscFieldsFromDay(input) {
		if (this.isMethodStd() === false || input.hasClass('std_disc_day') === false) {
			return false;
		}
		this.enableDisableStdDiscFieldsFromInputClass(input, 'std_disc_day');
	}

	/**
	 * Enable / Disable Other STD discount fields that arent input.std_disc_date
	 * @param {HTMLElement} input 
	 * @returns 
	 */
	enableDisableStdDiscFieldsFromDate(input) {
		if (this.isMethodStd() === false || input.hasClass('std_disc_date') === false) {
			return false;
		}
		this.enableDisableStdDiscFieldsFromInputClass(input, 'std_disc_date');
	}

	/**
	 * Enable / Disable input.std_due_day, input.std_due_date based off input.std_due_days
	 * @param	{HTMLElement} input 
	 * @returns {bool}
	 */
	enableDisableStdPrimaryDueFieldsFromDueDays(input) {
		if (this.isMethodStd() === false || input.hasClass('std_due_days') === false) {
			return false;
		}
		let days = input.val() == '' ? 0 : parseInt(input.val());
		let parentGroup = input.closest('.std-due');

		let inputs = [
			parentGroup.find('input.std_due_day'),
			parentGroup.find('input.std_due_date'),
		];

		let enableInputs = days == 0;
		this.enableDisableInputs(inputs, enableInputs);

		if (enableInputs === false) {
			let validator = input.closest('form').validate();
			inputs.forEach(input => {
				input.val('');
				validator.element('#' + input.attr('id'));
			});
		}
	}

	/**
	 * Enable / Disable input.std_plus_months based off input.std_due_day
	 * @param	{HTMLElement} input 
	 * @returns {bool}
	 */
	enableDisableStdDependentFieldsFromDueDay(input) {
		if (this.isMethodStd() === false || input.hasClass('std_due_day') === false) {
			return false;
		}
		let day = input.val() == '' ? 0 : parseInt(input.val());
		let parentGroup = input.closest('.std-due');
		let inputs = [
			parentGroup.find('input.std_plus_months')
		];

		let enableInputs = day > 0;
		this.enableDisableInputs(inputs, enableInputs);
	}

	/**
	 * Enable / Disable input.std_due_days, input.std_due_date based off input.std_due_day
	 * @param {HTMLElement} input 
	 * @returns {bool}
	 */
	enableDisableStdPrimaryDueFieldsFromDueDay(input) {
		if (this.isMethodStd() === false || input.hasClass('std_due_day') === false) {
			return false;
		}
		let day = input.val() == '' ? 0 : parseInt(input.val());
		let parentGroup = input.closest('.std-due');

		let inputs = [
			parentGroup.find('input.std_due_days'),
			parentGroup.find('input.std_due_date'),
		];

		let enableInputs = day == 0;
		this.enableDisableInputs(inputs, enableInputs);

		if (enableInputs === false) {
			let validator = input.closest('form').validate();
			inputs.forEach(input => {
				input.val('');
				validator.element('#' + input.attr('id'));
			});
		}
	}

	/**
	 * Enable / Disable input.std_due_days, input.std_due_day based off input.std_due_day
	 * @param	{HTMLElement} input 
	 * @returns {bool}
	 */
	enableDisableStdPrimaryDueFieldsFromDueDate(input) {
		if (this.isMethodStd() === false || input.hasClass('std_due_date') === false) {
			return false;
		}
		let parentGroup = input.closest('.std-due');

		let inputs = [
			parentGroup.find('input.std_due_days'),
			parentGroup.find('input.std_due_day'),
		];

		if (input.val() == '') {
			this.enableDisableInputs(inputs, true);
		}

		let dateRegexes  = DateRegexes.getInstance();
		let enableInputs = dateRegexes.regexes['mm/dd'].test(input.val()) === false;
		this.enableDisableInputs(inputs, enableInputs);

		if (enableInputs === false) {
			let validator = input.closest('form').validate();
			inputs.forEach(input => {
				input.val('');
				validator.element('#' + input.attr('id'));
			});
		}
	}

	/**
	 * Enable / Disable input.std_plus_years based off input.std_due_date
	 * @param	{HTMLElement} input 
	 * @returns {bool}
	 */
	enableDisableStdDependentFieldsFromDueDate(input) {
		if (this.isMethodStd() === false || input.hasClass('std_due_date') === false) {
			return false;
		}
		let parentGroup = input.closest('.std-due');
		
		let inputs = [
			parentGroup.find('input.std_plus_years'),
		];

		let dateRegexes = DateRegexes.getInstance();
		let enableInputs = dateRegexes.regexes['mm/dd'].test(input.val());
		this.enableDisableInputs(inputs, enableInputs);
	}

/* =============================================================
	Method STD
============================================================= */
	/**
	 * Update EOM Thru Day Input value
	 * @param {Object} input 
	 * @returns 
	 */
	updateEomThruDayInput(input) {
		if (this.isMethodEom === false || this.isInputEomThruDay(input) === false) {
			return false;
		}

		let value = input.val() == '' ? 0 : parseInt(input.val());

		if (value >= this.config.fields.eom_thru_day.defaultToMaxAt) {
			input.val(input.attr('max'));
		}
	}

	/**
	 * Setup values for Next Eom Split's Day Range
	 * @param {Object} input 
	 * @returns 
	 */
	setupNextEomSplits(input) {
		if (this.isMethodEom() === false || this.isInputEomThruDay(input) === false) {
			return false;
		}

		if (this.form.validate().element('#' + input.attr('id')) === false) {
			return false;
		}

		let index = parseInt(input.closest('.eom-split').data('index'));

		if (index >= codetable.config.methods.eom.splitCount) {
			return false;
		}

		let value = input.val() == '' ? 0 : parseInt(input.val());

		if (value === this.config.fields.eom_thru_day.max) {
			for (let i = index + 1; i <= codetable.config.methods.eom.splitCount; i++) {
				let split = $('.eom-split[data-index='+ i +']')
				split.find('input').val('');
			}
			return true;
		}

		for (let i = index; i <= codetable.config.methods.eom.splitCount; i++) {
			let nextIndex = i + 1;
			let split = $('.eom-split[data-index='+ i +']');
			let nextSplit = $('.eom-split[data-index='+ nextIndex +']');
			let thruDay = split.find('input.eom_thru_day').val() == '' ? 0 : parseInt(split.find('input.eom_thru_day').val());
			
			let nextFromDay = thruDay + 1;
			let nextThruDay = 99;

			if (nextIndex < codetable.config.methods.eom.splitCount && nextSplit.find('.eom_thru_day').val()) {
				nextThruDay = nextFromDay + 1;

				if (nextSplit.find('.eom_thru_day').val() != '') { // Keep value if already set
					nextThruDay = nextSplit.find('.eom_thru_day').val();
				}
			}

			if (thruDay == 99) {
				nextSplit.find('.eom_from_day').val('');
				nextSplit.find('.eom_thru_day').val('');
				continue;
			}

			if (nextIndex == codetable.config.methods.eom.splitCount) {
				nextThruDay = this.config.fields.eom_thru_day.max;
			}
			nextSplit.find('.eom_from_day').val(nextFromDay);
			nextSplit.find('.eom_thru_day').val(nextThruDay);
			this.updateEomThruDayInput(nextSplit.find('.eom_thru_day'));
		}
	}

	/**
	 * Enable / Disable Inputs for next EOM Split
	 * @param {Object} input 
	 * @returns 
	 */
	enableDisableNextEomSplits(input) {
		if (this.isMethodEom() === false || this.isInputEomThruDay(input) === false) {
			return false;
		}
		let index = parseFloat(input.closest('.eom-split').data('index'));

		if (index >= codetable.config.methods.eom.splitCount) {
			return false;
		}

		let validator = this.form.validate();
		let isValid = validator.element('#' + input.attr('id'));
		let value = input.val() == '' ? 0 : parseInt(input.val());
		let form = this;

		if (value == 99) {
			for (let i = (index + 1); i <= codetable.config.methods.eom.splitCount; i++) {
				let split = $('.eom-split[data-index=' + i + ']');
				split.find('input').each(function() {
					let sinput = $(this);
					form.disableTabindex(sinput);
					form.setReadonly(sinput, true);
					validator.element('#' + sinput.attr('id'));
				});
			}
		}

		for (let i = index; i <= codetable.config.methods.eom.splitCount; i++) {
			let nextIndex = i + 1;
			let split = $('.eom-split[data-index='+ i +']');
			let nextSplit = $('.eom-split[data-index='+ (nextIndex) +']');
			let isValid = validator.element('#' + split.find('input.eom_thru_day').attr('id'));
			let thruDay = split.find('input.eom_thru_day').val() == '' ? 0 : parseInt(split.find('input.eom_thru_day').val());

			// Disable next split's fields if Thru Day value is max or if it's invalid
			if (thruDay === this.config.fields.eom_thru_day.max || isValid === false) {
				this.disableTabindex(nextSplit.find('input.eom_thru_day'));
				this.setReadonly(nextSplit.find('input.eom_thru_day'), true);
				nextSplit.find('input').each(function() {
					let eomInput = $(this);
					form.setReadonly(eomInput, true);
					form.disableTabindex(eomInput);
				});
				return true;
			}
			this.setReadonly(nextSplit.find('input.eom_thru_day'), false);
			this.enableTabindex(nextSplit.find('input.eom_thru_day'));
			this.setReadonly(nextSplit.find('input.eom_disc_percent'), false);
			this.enableTabindex(nextSplit.find('input.eom_disc_percent'));

			if (nextIndex >= codetable.config.methods.eom.splitCount) {
				this.disableTabindex(nextSplit.find('.eom_thru_day'));
				this.setReadonly(nextSplit.find('input.eom_thru_day'), true);
			}

			nextSplit.find('.eom-due input').each(function() {
				let eomInput = $(this);
				form.setReadonly(eomInput, false);
				form.enableTabindex(eomInput);
			});
		}
	}

	/**
	 * Enable Discount / Day Month fields based off Discount Percent Value
	 * @param {Object} input 
	 * @returns 
	 */
	enableDisableEomDiscFieldsFromPercent(input) {
		if (this.isMethodEom() === false || input.hasClass('eom_disc_percent') === false) {
			return false;
		}

		let parent = input.closest('.eom-discount');
		let percent = input.val() == '' ? 0 : parseFloat(input.val());

		let inputDiscDay	= parent.find('.eom_disc_day');
		let inputDiscMonths = parent.find('.eom_disc_months');

		if (percent == 0) {
			this.setReadonly(inputDiscDay, true);
			this.disableTabindex(inputDiscDay);
			this.setReadonly(inputDiscMonths, true);
			this.disableTabindex(inputDiscMonths);
			return true;
		}
		this.setReadonly(inputDiscDay, false);
		this.enableTabindex(inputDiscDay);
		this.setReadonly(inputDiscMonths, false);
		this.enableTabindex(inputDiscMonths);
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

	/**
	 * Set / remove Readonly attribute on input
	 * @param	{Object} input 
	 * @param	{bool}	 readonly 
	 * @returns {bool}
	 */
	setReadonly(input, readonly = true) {
		if (input.attr('name') == undefined || input.attr('name') == '') {
			return false;
		}
		if (readonly === false) {
			input.removeAttr('readonly');
			return true;
		}
		input.attr('readonly', 'readonly');
	}

	/**
	 * Set / remove disabled attribute on input
	 * @param	{Object} input 
	 * @param	{bool}	 disable
	 * @returns {bool}
	 */
	setDisabled(input, disable = true) {
		if (input.attr('name') == undefined || input.attr('name') == '') {
			return false;
		}
		if (disable === false) {
			input.removeAttr('disabled');
			return true;
		}
		input.attr('disabled', 'disabled');
	}

	/**
	 * Enable tabindex attribute on input
	 * @param	{Object} input 
	 * @returns {bool}
	 */
	enableTabindex(input) {
		if (input.attr('name') == undefined || input.attr('name') == '') {
			return false;
		}
		let tabindex = isNaN(input.attr('tabindex')) ? '' : Math.abs(parseInt(input.attr('tabindex')));
		input.attr('tabindex', tabindex);
	}

	/**
	 * Disable tabindex attribute on input
	 * @param	{Object} input 
	 * @returns {bool}
	 */
	disableTabindex(input) {
		if (input.attr('name') == undefined || input.attr('name') == '') {
			return false;
		}
		let disablePrefix = '-';

		let tabindex = isNaN(input.attr('tabindex')) ? '' : Math.abs(parseInt(input.attr('tabindex')));

		if (tabindex == '') {
			tabindex = 1;
		}
		input.attr('tabindex', disablePrefix + tabindex);
	}

	/**
	 * Enable / Disable multiple Inputs
	 * @param	{array} inputs 
	 * @param	{bool}	enable 
	 * @returns {bool}
	 */
	enableDisableInputs(inputs, enable = true) {
		let formTrm = this;

		if (enable === false) {
			inputs.forEach(input => {
				formTrm.setReadonly(input, true);
				formTrm.disableTabindex(input);
			});
			return true;
		}
		
		inputs.forEach(input => {
			formTrm.setReadonly(input, false);
			formTrm.enableTabindex(input);
		});
		return true;
	}
}
