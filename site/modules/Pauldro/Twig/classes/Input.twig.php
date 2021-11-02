<?php namespace Twig\Html;

class TwigInput extends TwigBaseHtml {
	const SIZES = ['xs', 'sm', 'md', 'lg', 'xl'];

	const DEFAULTS = [
		'type'       => 'text',
		'inputclass' => 'form-control',
		'name'       => '',
		'id'         => '',
		'value'      => '',
		'size'       => '',
		'addclasses' => [],
		'attributes' => [],
		'uppercase'  => false
	];

	const ATTRIBUTES_NOVALUE = [
		'readonly',
		'disabled',
		'autofocus'
	];

	public function __construct() {
		$this->type = 'text';
		$this->inputclass = 'form-control';
		$this->size       = '';
		$this->name  = '';
		$this->id    = '';
		$this->value = '';
		$this->addclasses = [];
		$this->attributes = [];
		$this->uppercase  = false;
	}

	/**
	 * Return id value for Input
	 * NOTE: Will Return Name as id if blank
	 * @return string
	 */
	public function id() {
		return $this->id ? $this->id : $this->name;
	}

	/**
	 * Return class
	 * NOTE: Takes Input class and adds additionally supplied classes
	 * @return string
	 */
	public function class() {
		$base = self::DEFAULTS['inputclass'];
		$class = $this->inputclass;

		if (in_array($this->size, self::SIZES)) {
			$class .= " $base-$this->size";
		}
		$class .= ' ' . implode(' ', $this->addclasses);
		return trim($class);
	}

	public function attributes() {
		if ($this->uppercase === true) {
			$attributes = $this->attributes;
			$attributes['oninput'] = 'this.value = this.value.toUpperCase()';
			$this->attributes = $attributes;
		}
		return parent::attributes();
	}
}
