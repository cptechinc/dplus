<?php namespace Twig\Html;

use ProcessWire\WireData;

abstract class TwigBaseHtml extends WireData {
	const ATTRIBUTES_NOVALUE = [];

	/**
	 * Return class
	 * @return string
	 */
	abstract public function class();

	/**
	 * Return attributes string
	 * @return string    e.g. placeholder="{}" max="{}" disabled
	 */
	public function attributes() {
		$attr = [];
		foreach ($this->attributes as $key => $value) {
			if (in_array($key, $this::ATTRIBUTES_NOVALUE)) {
				$attr[] = $value === true ? $key : '';
			} else {
				$attr[] = "$key=\"$value\"";
			}
		}
		return trim(implode(' ', array_filter($attr)));
	}

	/**
	 * Set Properties from Object Values
	 * @param  stdClass $obj
	 * @return void
	 */
	public function setFromObj($obj) {
		$attributes = get_object_vars($obj);
		$this->setFromArray($attributes);
	}

	/**
	 * Set Properties from Array (key-value)
	 * @param  array $attributes
	 * @return void
	 */
	public function setFromArray(array $attributes) {
		foreach ($attributes as $attribute => $value) {
			if (array_key_exists($attribute, $this->data) && is_null($value) === false) {
				$this->$attribute = $value;
			}
		}
	}
}
