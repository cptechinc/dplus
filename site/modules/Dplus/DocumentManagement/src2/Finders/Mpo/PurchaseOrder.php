<?php namespace Dplus\Docm\Finders\Mpo;
// Dplus Model
use DocumentQuery;
use PurchaseOrder as PoModel;
// Dplus Docm
use Dplus\Docm\Finders\Finder\TagRef1;

/**
 * PurchaseOrder
 * Decorator for DocumentQuery to find Documents in Database related to Purchase Order #
 */
class PurchaseOrder extends TagRef1 {
	const TAG = ['PO'];

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
	 * Return Documents related to Purchase Order #
	 * @param  string $ponbr  Purchase Order #
	 * @return ObjectCollection|Document[]
	 */
	public function find($ponbr) {
		return parent::find(PoModel::get_paddedponumber($ponbr));
	}

	/**
	 * Return the number of Documents related to Purchase Order #
	 * @param  string $ponbr  Purchase Order #
	 * @return int
	 */
	public function count($ponbr) {
		return parent::count(PoModel::get_paddedponumber($ponbr));
	}

/* =============================================================
	Query Decorator Functions
============================================================= */
	/**
	 * Add Vendor PO Condition to DocumentQuery
	 * @param  DocumentQuery  $q       Document Query
	 * @param  string|array   $invnbr  AP Invoice Number
	 * @param  string         $name    Condition Name
	 * @return string
	 */
	public function addConditionPonbr(DocumentQuery $q, $ponbr, $name = 'cond_vendorpo') {
		$columns = self::getColumns();
		$list = $this->wire('sanitizer')->array($ponbr);

		$q->condition('tag_vendorpo', "Document.{$columns->tag} = ?", self::TAG[0]);
		$q->condition('ref1_vendorpo', "Document.{$columns->reference1} IN ?", $list);
		$q->combine(array('tag_vendorpo', 'reference1_vendorpo'), 'and', $name);
		return $name;
	}
}
