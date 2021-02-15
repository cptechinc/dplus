<?php namespace Twig\Html;

class TwigDatePicker extends TwigInputGroup {
	const DEFAULTS = [
		'baseclass'  => 'input-group',
		'type'       => 'apppend',
		'size'       => '',
		'input'      => [],
		'button'     => []
	];

	const ATTRIBUTES_NOVALUE = [
		'readonly',
		'disabled'
	];

	const SIZES = ['xs', 'sm', 'md', 'lg', 'xl'];
	const TYPES = ['append'];

	/**
	 * Return Class for Input Group
	 * @return string
	 */
	public function class() {
		$classes = $this->addclasses;
		$classes[] = 'datepicker';
		$this->addclasses = $classes;
		return parent::class();
	}

	/**
	 * Set Properties from Array (key-value)
	 * @param  array $attributes
	 * @return void
	 */
	public function setFromArray(array $array) {
		parent::setFromArray($array);
		
		$input = $this->input;
		$classes = array_key_exists('addclasses', $this->input) ? $this->input['addclasses'] : [];
		$classes[] = 'date-input';
		$input['addclasses'] = $classes;
		$this->input = $input;
	}
}
