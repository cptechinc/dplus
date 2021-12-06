<?php namespace Dplus\Mso\So;
// Dplus Model
use SalesOrderQuery, SalesOrder;
// ProcessWire
use ProcessWire\WireData;

/**
 * Sales Order Tools
 */
class Tools extends WireData {
	private static $instance;

	public static function getInstance() {
		if (empty(self::$instance)) {
			$instance = new self();
			self::$instance = $instance;
		}
		return self::$instance;
	}

	/**
	 * Return Query
	 * @return SalesOrderQuery
	 */
	public function query() {
		return SalesOrderQuery::create();
	}

	/**
	 * Return If Sales Order Number Exists
	 * @param  string $ordn Order Number
	 * @return bool
	 */
	public function exists($ordn) {
		return boolval($this->query()->filterByOrdernumber($ordn)->count());
	}

	/**
	 * Return SalesOrder
	 * @param  string $ordn Order Number
	 * @return SalesOrder
	 */
	public function order($ordn) {
		return $this->query()->filterByOrdernumber($ordn)->findOne();
	}

	public function getLotAllocatorM() {
		return AllocatedLots::getInstance();
	}
}
