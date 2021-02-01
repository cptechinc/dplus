<?php namespace Twig\Html;

class TwigLink extends TwigBaseHtml {

	public function __construct() {
		$this->href = '#';
		$this->addclasses = [];
		$this->text       = '';
		$this->attributes = [];
	}

	/**
	 * Return Class for button
	 * @return string
	 */
	public function class() {
		$class = implode(' ', $this->addclasses);
		return trim($class);
	}
}