<?php namespace Twig\Html;

class TwigInputGroup extends TwigBaseHtml {
	const DEFAULTS = [
		'baseclass'  => 'input-group',
		'type'       => 'prepend',
		'size'       => '',
	];

	const ATTRIBUTES_NOVALUE = [
		'readonly',
		'disabled'
	];

	const SIZES = ['xs', 'sm', 'md', 'lg', 'xl'];
	const TYPES = ['prepend', 'append'];

	public function __construct() {
		$this->type = 'prepend';
		$this->size = '';
		$this->attributes = [];
		$this->input = [];
		$this->p     = [];
		$this->button = [];
		$this->span = [];
		$this->addclasses = [];
	}

	/**
	 * Return Class for Input Group
	 * @return string
	 */
	public function class() {
		$base = self::DEFAULTS['baseclass'];
		$class = $base;
		if (in_array($this->size, self::SIZES)) {
			$class .= " $base-$this->size";
		}
		$class .= ' ' . implode(' ', $this->addclasses);
		return trim($class);
	}

	/**
	 * Set Properties from Array (key-value)
	 * @param  array $attributes
	 * @return void
	 */
	public function setFromArray(array $array) {
		foreach ($array as $key => $value) {
			if (array_key_exists($key, $this->data)) {
				$this->$key = $value;
			}
		}

		if (array_key_exists('attributes', $array)) {
			$attributes = $array['attributes'];

			if (array_key_exists('disabled', $attributes)) {
				$input = $this->input;
				$input['attributes']['disabled']  = $attributes['disabled'];
				$this->input = $input;

				$button = $this->button;
				$button['attributes']['disabled'] = $attributes['disabled'];
				$this->button = $button;
			}

			if (array_key_exists('readonly', $attributes)) {
				$input = $this->input;
				$input['attributes']['readonly']  = $attributes['readonly'];
				$this->input = $input;

				$button = $this->button;
				$button['attributes']['disabled'] = $attributes['readonly'];
				$this->button = $button;
			}
		}
	}
}
