<?php namespace Dplus\Docm;
// Dplus Models
use Document;
use DocumentQuery as Query;
// ProcessWire
use ProcessWire\WireData;
// Dplus Docm
use Dplus\Docm\Documents;

/**
 * Docm\Finder
 * Base Class for DocumentQuery Wrappers / Decorators
 */
abstract class Finder extends WireData {
	private static $columns;

	/**
	 * Return Query Filtered By Folder(s)
	 * @return Query
	 */
	public function query() {
		return Documents::instance()->query();
	}

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