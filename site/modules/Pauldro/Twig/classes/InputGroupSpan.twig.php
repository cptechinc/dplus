<?php namespace Twig\Html;

class TwigInputGroupSpan extends TwigBaseHtml {
	const DEFAULTS = [
		'baseclass'  => 'input-group-text',
		'colorclass'  => '',
		'size'       => '',
		'addclasses' => [],
		'attributes' => []
	];

	const ATTRIBUTES_NOVALUE = [
		'readonly',
		'disabled'
	];

	const TYPES = ['button', 'submit'];
	const SIZES = ['xs', 'sm', 'md', 'xl'];

	public function __construct() {
		$this->baseclass  = self::DEFAULTS['baseclass'];
		$this->colorclass = self::DEFAULTS['colorclass'];
		$this->size       = '';
		$this->addclasses = [];
		$this->text       = '';
		$this->attributes = [];
	}

	/**
	 * Return Class for button
	 * @return string
	 */
	public function class() {
		$classes = [self::DEFAULTS['baseclass']];
		if (in_array($this->size, self::SIZES)) {
			$classes[] = "btn-$this->size";
		}
		$classes[] = $this->colorclass;
		$classes = array_merge($classes, $this->addclasses);
		$class = implode(' ', $classes);
		return trim($class);
	}

}
