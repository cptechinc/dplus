<?php namespace Dplus\Docm\Finders\Mar;
// Dplus Model
use SalesOrder as SoModel;
use DocumentQuery;
// Dplus Docm
use Dplus\Docm\Finders\Finder\TagRef1;

/**
 * ArInvoice
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
	/**
	 * Add Invoice Condition to Document Query
	 * @param  DocumentQuery $q
	 * @param  string        $invnbr     Invoice Number
	 * @param  strin         $name       Conditon Name
	 * @return string
	 */
	protected function addConditionInvnbr(DocumentQuery $q, $invnbr, $name = 'cond_invoices') {
		$columns = self::getColumns();
		$q->condition('tag_invoices', "Document.{$columns->tag} = ?", self::TAG[0]);
		$q->condition('reference1_invoices', "Document.{$columns->reference1} = ?", $invnbr);
		$q->combine(array('tag_invoices', 'reference1_invoices'), 'and', $name);
		return $name;
	}
}
