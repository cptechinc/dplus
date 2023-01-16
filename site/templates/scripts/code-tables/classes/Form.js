class CodeFormBase {
	static instance = null;

	static getInstance() {
		if (this.instance === null) {
			this.instance = new CodeFormBase();
		}
		return this.instance;
	}

	constructor() {
		this.id = 'code-form';
		this.form = $('#' + this.id);
		this.inputs = CodeInputsBase.getInstance();
		this.config = CodeConfigBase.getInstance();
	}

	updateInputsFromJson(json = null) {
		this.updateDeleteLink(json);

		if (json) {
			for (const [key, input] of Object.entries(this.inputs.fields)) {
				this.setInputValue(input, json[key]);
			}
			this.inputs.fields.json.val(JSON.stringify(json));
			this.inputs.form.attr('data-code', json.code);
			this.inputs.fields.code.attr('readonly', 'true');
			var validator = this.inputs.form.validate();
			validator.element('#' + this.inputs.fields.code.attr('id'));
			this.enableDisableInputs();
			this.setDatepickerFormattedDates();
			this.inputs.form.trigger('update.codetable.form');
			return true;
		}
		this.clearInputs();
		this.clearAjaxDescriptions();
		this.setDefaultValues();
		this.setDatepickerFormattedDates();
		this.enableDisableInputs();
		this.inputs.form.attr('data-code', '');
		this.inputs.fields.code.removeAttr('readonly');
		this.inputs.form.trigger('update.codetable.form');
	}

	setDefaultValues() {
		for (const [key, field] of Object.entries(this.config.fields)) {
			if (field.hasOwnProperty('default')) {
				let input = this.inputs.fields[key];
				input.val(field.default);
			}
		}
	}

	enableDisableInputs() {
		for (const [key, field] of Object.entries(this.config.fields)) {
			if (field.hasOwnProperty('disabled')) {
				let input = this.inputs.fields[key];
				if (field.disabled === true) {
					input.attr('disabled', 'disabled');
				}
				if (field.disabled != true) {
					input.removeAttr('disabled');
				}
			}
		}
	}

	clearInputs(force = false) {
		for (const [key, input] of Object.entries(this.inputs.fields)) {
			if (input.data('readonly')) {
				continue;
			}
			
			if (force == false) {
				if (input.data('dontclear')) {
					continue;
				}
			}
			this.setInputValue(input, '');
		}
	}

	clearAjaxDescriptions() {
		this.inputs.form.find('.ajax-description').text('');
	}

	setDatepickerFormattedDates() {
		this.inputs.form.find('.date-input').each(function () {
			let input = $(this);

			if (parseInt(input.val()) == 0) {
				input.val('');
			}

			let regex = new RegExp(config.regex.date.ymd);

			if (input.val() != '' && regex.test(input.val())) {
				input.val(moment(input.val()).format('MM/DD/YYYY'));
			}
		});
	}

	setInputValue(input, val) {
		input.val(val);
		
		if (input.attr('data-triggerchange')) {
			input.change();
		}
	}

	updateDeleteLink(json = null) {
		var button = this.form.find('a.delete');
		if (button.length == 0) {
			return false;
		}
		var uri = URI(button.attr('href'));
		var data = uri.query(true);

		if (json) {
			data.code = json.code;
			uri.query(data);
			button.attr('href', uri.toString());
			button.addClass('show');
			return true;
		}
		data.code = '';
		uri.query(data);
		button.attr('href', uri.toString());
		button.removeClass('show');
		return true;
	}

	serializeForm(serialize = true) {
		if (serialize == false) {
			this.form.attr('data-serialized', '');
			return true;
		}
		this.form.attr('data-serialized', this.form.serialize());
	}
}
