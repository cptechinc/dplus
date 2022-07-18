<?php namespace Dplus\Mso\So\SalesOrder;
// Dplus Models
use SalesOrderDetailQuery, SalesOrderDetail;
// ProcessWire
use ProcessWire\WireData;

/**
 * So\SalesOrder\Details
 * 
 * Wrapper for Querying Sales Order Detail Database
 */
class Details extends WireData {
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
	 * @return SalesOrderDetailQuery
	 */
	public function query() {
		return SalesOrderDetailQuery::create();
	}

	/**
	 * Return Query filtered by Order #
	 * @return SalesOrderDetailQuery
	 */
	public function queryOrdernumber($ordn) {
		$q = $this->query();
		$q->filterByOrdernumber($ordn);
		return $q;
	}

	/**
	 * Return Query filtered by Order #, Line #
	 * @return SalesOrderDetailQuery
	 */
	public function queryOrdernumberLinenbr($ordn, int $linenbr = 0) {
		$q = $this->queryOrdernumber($ordn);
		$q->filterByLinenbr($linenbr);
		return $q;
	}

/* =============================================================
	Read Functions
============================================================= */
	/**
	 * Return if Sales Order Detail Line Exists
	 * @param  string  $ordn
	 * @param  int     $linenbr
	 * @return bool
	 */
	public function exists($ordn, int $linenbr = 0) {
		$q = $this->queryOrdernumberLinenbr($ordn, $linenbr);
		return boolval($q->count());
	}

	/**
	 * Return Sales Order Detail Line
	 * @param  string  $ordn
	 * @param  int     $linenbr
	 * @param  bool    $createIfNotFound 
	 * @return SalesOrderDetail
	 */
	public function detailLine($ordn, int $linenbr = 0, $createIfNotFound = false) {
		$q = $this->queryOrdernumberLinenbr($ordn, $linenbr);
		if ($createIfNotFound === false) {
			return $q->findOne();
		}
		return $q->findOneOrCreate();
	}
}