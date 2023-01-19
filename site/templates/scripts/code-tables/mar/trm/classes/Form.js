class TrmForm extends CodeFormBase {
	static instance = null;

	static getInstance() {
		if (this.instance === null) {
			this.instance = new TrmForm();
		}
		return this.instance;
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

	sumUpStdOrderPercents() {
		let formStd = this.form.find('#std-splits');
		let total = 0.00;
		let form = this;

		formStd.find('input.order_percent').each(function() {
			let input = $(this);
			let percent = parseFloat(input.val());
			if (isNaN(percent)) {
				percent = 0.0;
			}
			percent = percent.toFixed(form.config.fields.order_percent.precision);
			total = parseFloat(total) + parseFloat(percent);
		});
		return total.toFixed(form.config.fields.order_percent.precision);
	}

	enableDisableNextStdSplit(input) {
		if (this.isMethodStd() === false || input.hasClass('order_percent') === false) {
			return false;
		}
		let thisSplit = input.closest('.std-split');
		let index = parseFloat(thisSplit.data('index'));

		if (index >= codetable.config.methods.std.splitCount) {
			return false;
		}
		let formStd = this.form.find('#std-splits');
		
		let totalPercent = this.sumUpStdOrderPercents();

		if (totalPercent > 100) {
			formStd.find('#std-error').text("Order Percent total is more than 100");
		}

		// let validator = this.form.validate();
		// let isValid = validator.element('#' + input.attr('id'));
		let value = input.val() == '' ? 0.0 : parseFloat(input.val());

		let nextIndex  = index + 1;
		let nextSplit = $('.std-split[data-index='+ (nextIndex) +']');
		// let form = this;

		let nextInputs = [
			nextSplit.find('input.order_percent'),
			nextSplit.find('input.std_disc_percent'),
			nextSplit.find('input.std_due_days'),
			nextSplit.find('input.std_due_day'),
			nextSplit.find('input.std_plus_months'),
			nextSplit.find('input.std_due_date'),
			nextSplit.find('input.std_plus_years'),
		]

		if (value == 0) {
			this.enableDisableInputs(nextInputs, false);
			let splitInputs = [
				thisSplit.find('input.order_percent'),
				thisSplit.find('input.std_disc_percent'),
				thisSplit.find('input.std_due_days'),
				thisSplit.find('input.std_due_day'),
				thisSplit.find('input.std_plus_months'),
				thisSplit.find('input.std_due_date'),
				thisSplit.find('input.std_plus_years'),
			]
			this.enableDisableInputs(splitInputs, false);
			return true;
		}

		if (totalPercent < 100) {
			this.enableDisableInputs(nextInputs, true);
		}

		if (totalPercent == 100) {
			this.enableDisableInputs(nextInputs, false);
		}
	}

	/**
	 * Setup values for Next Eom Split's Day Range
	 * @param {Object} input 
	 * @returns 
	 */
	setupNextStdSplit(input) {
		if (this.isMethodStd() === false || input.hasClass('order_percent') === false) {
			return false;
		}
		let thisSplit = input.closest('.std-split');
		let index = parseFloat(thisSplit.data('index'));

		if (index >= codetable.config.methods.splitCount) {
			return false;
		}

		let value = input.val() == '' ? 0 : parseFloat(input.val());
		let totalPercent = this.sumUpStdOrderPercents();
		let nextIndex = index + 1;
		let nextSplit = $('.std-split[data-index='+ (nextIndex) +']');


		if (totalPercent == 100) {
			return true;
		}

		if (totalPercent < 100) {
			let nextPercent = parseFloat(100 - totalPercent);
			let nextInputPercent = nextSplit.find('input.order_percent');
			if (nextInputPercent.val()) {
				nextPercent += parseFloat(nextInputPercent.val());
			}
			nextInputPercent.val(nextPercent.toFixed(this.config.fields.order_percent.precision))
			nextInputPercent.change();
		}
	}


	/**
	 * Enable Discount / Day Month fields based off Discount Percent Value
	 * @param {Object} input 
	 * @returns 
	 */
	enableDisableStdDiscFieldsFromDiscPercent(input) {
		if (this.isMethodStd() === false) {
			return false;
		}
		if (input.hasClass('std_disc_percent') === false) {
			return false;
		}
		let parent  = input.closest('.std-discount');
		let percent = input.val() == '' ? 0 : parseFloat(input.val());

		let inputDiscDays	= parent.find('.std_disc_days');
		let inputDiscDay	= parent.find('.std_disc_day');
		let inputDiscDate   = parent.find('.std_disc_date');

		if (percent == 0) {
			this.setReadonly(inputDiscDays, true);
			this.disableTabindex(inputDiscDays);
			this.setReadonly(inputDiscDay, true);
			this.disableTabindex(inputDiscDay);
			this.setReadonly(inputDiscDate, true);
			this.disableTabindex(inputDiscDate);
			return true;
		}
		this.setReadonly(inputDiscDays, false);
		this.enableTabindex(inputDiscDays);
		this.setReadonly(inputDiscDay, false);
		this.enableTabindex(inputDiscDay);
		this.setReadonly(inputDiscDate, false);
		this.enableTabindex(inputDiscDate);
	}

	/**
	 * Enable / Disable input.std_due_day, input.std_due_date based off input.std_due_days
	 * @param {HTMLElement} input 
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
	}

	/**
	 * Enable / Disable input.std_plus_months based off input.std_due_day
	 * @param {HTMLElement} input 
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
	}

	/**
	 * Enable / Disable input.std_due_days, input.std_due_day based off input.std_due_day
	 * @param {HTMLElement} input 
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
	}

	/**
	 * Enable / Disable input.std_plus_years based off input.std_due_date
	 * @param {HTMLElement} input 
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
	Method EOM Events
============================================================= */
	/**
	 * Enable Discount / Day Month fields based off Discount Percent Value
	 * @param {Object} input 
	 * @returns 
	 */
	enableDisableEomDiscFieldsFromPercent(input) {
		if (this.isMethodEom()) {
			return false;
		}
		if (input.hasClass('eom_disc_percent') === false) {
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

		if (value > this.config.fields.eom_thru_day.defaultToMaxAt) {
			input.val(input.attr('max'));
		}
	}

	/**
	 * Enable / Disable Inputs for next EOM Split
	 * @param {Object} input 
	 * @returns 
	 */
	enableDisableNextEomSplit(input) {
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
		let nextIndex  = index + 1;
		let nextSplit = $('.eom-split[data-index='+ (nextIndex) +']');
		let form = this;

		
		// Disable next split's fields if Thru Day value is max or if it's invalid
		if (value === this.config.fields.eom_thru_day.max || isValid === false) {
			this.disableTabindex(nextSplit.find('input.eom_thru_day'));
			this.setReadonly(nextSplit.find('input.eom_thru_day'), true);
			nextSplit.find('.eom-discount input').each(function() {
				let eomInput = $(this);
				form.setReadonly(eomInput, true);
				form.disableTabindex(eomInput);
			});
			nextSplit.find('.eom-due input').each(function() {
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

		nextSplit.find('.eom-due input').each(function() {
			let eomInput = $(this);
			form.setReadonly(eomInput, false);
			form.enableTabindex(eomInput);
		});

		if (nextIndex == codetable.config.methods.eom.splitCount) {
			this.disableTabindex(nextSplit.find('.eom_thru_day'));
			this.setReadonly(nextSplit.find('input.eom_thru_day'), true);
		}
	}

	/**
	 * Setup values for Next Eom Split's Day Range
	 * @param {Object} input 
	 * @returns 
	 */
	setupNextEomSplit(input) {
		if (this.isMethodEom() === false || this.isInputEomThruDay(input) === false) {
			return false;
		}

		let index = parseFloat(input.closest('.eom-split').data('index'));

		if (index >= codetable.config.methods.splitCount) {
			return false;
		}

		let value = input.val() == '' ? 0 : parseInt(input.val());
		let nextIndex = index + 1;
		let nextSplit = $('.eom-split[data-index='+ (nextIndex) +']');

		if (value === this.config.fields.eom_thru_day.max) {
			nextSplit.find('input').val('');
			return true;
		}
		nextSplit.find('.eom_from_day').val(value+1);
		nextSplit.find('.eom_thru_day').val(this.config.fields.eom_thru_day.max);
	}

/* =============================================================
	Supplemental
============================================================= */
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
	 * @param   {array} inputs 
	 * @param   {bool}  enable 
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
