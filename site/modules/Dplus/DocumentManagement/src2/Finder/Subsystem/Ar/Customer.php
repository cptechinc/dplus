<?php namespace Dplus\Docm\Finder\Subsystem\Ar;
// Dplus Model
use DocumentQuery;
// Dplus Docm
use Dplus\Docm\Finder\TagRef1;

/**
 * Finder\Subsystem\Ar\Customer
 * Decorator for DocumentQuery to find Documents in Database related to AR Customer ID
 */
class Customer extends TagRef1 {
	const TAG = ['CU'];

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

/* =============================================================
	Query Decorator Functions
============================================================= */
	/**
	 * Add Invoice Condition to Document Query
	 * @param  DocumentQuery $q
	 * @param  string        $custID  Customer ID
	 * @param  strin         $name    Conditon Name
	 * @return string
	 */
	protected function addConditionCustid(DocumentQuery $q, $custID, $name = 'cond_customer') {
		$columns = self::getColumns();
		$q->condition('tag_customer', "Document.{$columns->tag} = ?", self::TAG[0]);
		$q->condition('reference1_customer', "Document.{$columns->reference1} = ?", $custID);
		$q->combine(array('tag_customer', 'reference1_customer'), 'and', $name);
		return $name;
	}
}
