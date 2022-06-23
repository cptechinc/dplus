<?php namespace Dplus\Docm\Finder\Subsystem\Ar;
// Dplus Model
use SalesOrder as SoModel;
// Dplus Docm
use Dplus\Docm\Finder\TagRef1;

/**
 * Finder\Subsystem\Ar\ArInvoice
 * Decorator for DocumentQuery to find Documents in Database related to AR Invoice #
 */
class ArInvoice extends TagRef1 {
	const TAG = ['AR'];

	protected static $instance;

	/** @return self */
	public static function instance() {
		if (empty(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/* =============================================================
		Read Functions
	============================================================= */
	/**
	 * Return Documents related to Invoice #
	 * @param  string $invnbr  Invoice #
	 * @return ObjectCollection|Document[]
	 */
	public function find($invnbr) {
		return parent::find(SoModel::get_paddedordernumber($invnbr));
	}

	/**
	 * Return the number of Documents related to Invoice #
	 * @param  string $invnbr  Invoice #
	 * @return int
	 */
	public function count($invnbr) {
		return parent::count(SoModel::get_paddedordernumber($invnbr));
	}

	/* =============================================================
		Query Decorator Functions
	============================================================= */
}
