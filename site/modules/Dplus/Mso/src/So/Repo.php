<?php namespace Dplus\Mso\So;
// Dplus Models
use SalesOrderQuery, SalesOrder;
// ProcessWire
use ProcessWire\WireData;

/**
 * So\Repo
 * 
 * Wrapper for Querying Sales Order Database
 */
class Repo extends WireData {
	private static $instance;

	/** @return self */
	public static function instance() {
		if (empty(self::$instance)) {
			$instance = new self();
			self::$instance = $instance;
		}
		return self::$instance;
	}

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Query
	 * @return SalesOrderQuery
	 */
	public function query() {
		return SalesOrderQuery::create();
	}

	/**
	 * Return Query filtered by Order #
	 * @return SalesOrderQuery
	 */
	public function queryOrdernumber($ordn) {
		$q = $this->query();
		$q->filterByOrdernumber($ordn);
		return $q;
	}

/* =============================================================
	Read Functions
============================================================= */
	/**
	 * Return if Order Exists
	 * @param  string $ordn
	 * @return bool
	 */
	public function exists($ordn) {
		$q = $this->queryOrdernumber($ordn);
		return boolval($q->count());
	}

	/**
	 * Return Order
	 * @param  string $ordn
	 * @return SalesOrder
	 */
	public function order($ordn) {
		return $this->queryOrdernumber($ordn)->findOne();
	}

	/**
	 * Return Order Customer ID
	 * @param  string $ordn
	 * @return string
	 */
	public function custid($ordn) {
		$q = $this->queryOrdernumber($ordn);
		$q->select(SalesOrder::aliasproperty('custid'));
		return $q->findOne();
	}
}