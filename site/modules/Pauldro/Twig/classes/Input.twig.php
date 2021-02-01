<?php namespace Twig\Html;

class TwigInput extends TwigBaseHtml {
	const DEFAULTS = [
		'type'       => 'text',
		'inputclass' => 'form-control',
		'name'       => '',
		'id'         => '',
		'value'      => '',
		'addclasses' => [],
		'attributes' => []
	];

	const ATTRIBUTES_NOVALUE = [
		'readonly',
		'disabled'
	];

	public function __construct() {
		$this->type = 'text';
		$this->inputclass = 'form-control';
		$this->name = '';
		$this->id   = '';
		$this->value = '';
		$this->addclasses = [];
		$this->attributes = [];
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
		$class = $this->inputclass;
		$class .= ' ' . implode(' ', $this->addclasses);
		return trim($class);
	}
}