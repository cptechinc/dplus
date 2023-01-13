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
	 * Return if Input is EOM thru day
	 * @param {Object} input 
	 * @returns bool
	 */
	isInputEomThruDay(input) {
		return input.hasClass('eom_thru_day');
	}

	/**
	 * Enable Discount / Day Month fields based off Discount Percent Value
	 * @param {Object} input 
	 * @returns 
	 */
	enableDisableEomDiscDayMonthFromPercent(input) {
		if (this.inputs.fields.method.val() != codetable.config.methods.eom.value){
			return false;
		}
		var parent = input.closest('.eom-discount');
		var percent = input.val() == '' ? 0 : parseFloat(input.val());

		if (percent == 0) {
			parent.find('.eom_disc_day').attr('readonly', 'readonly');
			parent.find('.eom_disc_months').attr('readonly', 'readonly');
			return true;
		}
		parent.find('.eom_disc_day').removeAttr('readonly');
		parent.find('.eom_disc_months').removeAttr('readonly');
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

		var value = input.val() == '' ? 0 : parseInt(input.val());
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
		if (this.isMethodEom === false || this.isInputEomThruDay(input) === false) {
			return false;
		}
		var index = parseFloat(input.closest('.eom-split').data('index'));

		if (index >= codetable.config.methods.splitCount) {
			return false;
		}

		var validator = this.form.validate();
		var isValid = validator.element('#' + input.attr('id'));

		var value = input.val() == '' ? 0 : parseInt(input.val());
		var nextSplit = $('.eom-split[data-index='+ (index + 1) +']');

		// Disable next split's fields if Thru Day value is max or if it's invalid
		if (value === this.config.fields.eom_thru_day.max || isValid === false) {
			nextSplit.find('.eom-day-range input.eom_thru_day').attr('readonly', 'readonly');
			nextSplit.find('.eom-discount input').attr('readonly', 'readonly');
			nextSplit.find('.eom-due input').attr('readonly', 'readonly');
			return true;
		}
		nextSplit.find('.eom-day-range input.eom_thru_day').removeAttr('readonly');
		nextSplit.find('input.eom_disc_percent').removeAttr('readonly');
		nextSplit.find('.eom-due input').removeAttr('readonly');
	}

	/**
	 * Setup values for Next Eom Split's Day Range
	 * @param {Object} input 
	 * @returns 
	 */
	setupNextEomSplit(input) {
		if (this.isMethodEom === false || this.isInputEomThruDay(input) === false) {
			return false;
		}

		var index = parseFloat(input.closest('.eom-split').data('index'));

		if (index >= codetable.config.methods.splitCount) {
			return false;
		}

		var value = input.val() == '' ? 0 : parseInt(input.val());
		var nextSplit = $('.eom-split[data-index='+ (index + 1) +']');

		if (value === this.config.fields.eom_thru_day.max) {
			nextSplit.find('input').val('');
			return true;
		}
		nextSplit.find('.eom_from_day').val(value+1);
		nextSplit.find('.eom_thru_day').val(this.config.fields.eom_thru_day.max);
	}
}
