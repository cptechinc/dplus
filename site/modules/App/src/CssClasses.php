<?php namespace App;
// ProcessWire
use ProcessWire\WireData;

/**
 * Stores CSS classes for HTML use
 */
class CssClasses extends WireData {
	const CLASSES = [
		'icons' => [
			'edit'     => 'fa fa-pencil',
			'delete'   => 'fa fa-trash',
			'menu'     => 'fa fa-list',
			'list'     => 'fa fa-list',
			'function' => 'fa fa-microchip',
		]
	];

	private static $instance;

	public static function instance() {
		if (empty(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	public function __construct() {
		$this->setArray(self::CLASSES);
	}
}