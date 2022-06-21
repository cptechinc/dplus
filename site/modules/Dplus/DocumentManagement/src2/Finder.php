<?php namespace Dplus\Docm;
// Dplus Models
use Document;
// ProcessWire
use ProcessWire\WireData;

/**
 * Docm\Finder
 * Base Class for DocumentQuery Wrappers / Decorators
 */
abstract class Finder extends WireData {
	private static $columns;

/* =============================================================
	Supplemental Functions
============================================================= */
	/**
	 * Return Columns
	 * @return WireData
	 */
	public static function getColumns() {
		if (empty(self::$columns === false)) {
			return self::$columns; 
		}
		$columns = new WireData();
		$columns->tag = Document::aliasproperty('tag');
		$columns->reference1 = Document::aliasproperty('reference1');
		$columns->reference2 = Document::aliasproperty('reference2');
		self::$columns = $columns;
		return self::$columns;
	}
}