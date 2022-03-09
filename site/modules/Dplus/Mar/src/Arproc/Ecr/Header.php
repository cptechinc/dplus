<?php namespace Dplus\Mar\Arproc\Ecr;
// Dplus Model
use ArCashHeadQuery, ArCashHead;
// ProcessWire
use ProcessWire\WireData;

/**
 * Header
 *
 * Handles CRUD for ArCashHead
 */
class Header extends WireData {
	private static $instance;

	/**
	 * Return Instance
	 * @return self
	 */
	public static function instance($custID = '') {
		if (empty(self::$instance)) {
			self::$instance = new self($custID);
		}
		return self::$instance;
	}

	public function __construct($custID = '') {
		$this->custID = $custID;
	}

	/**
	 * Return Query
	 * @return ArCashHeadQuery
	 */
	public function query() {
		return ArCashHeadQuery::create();
	}

	/**
	 * Return Query filtered by Customer ID
	 * @param  string $custID Customer ID
	 * @return ArCashHeadQuery
	 */
	public function queryCustid($custID) {
		return $this->query()->filterByCustid($custID);
	}

	/**
	 * Return if Header for Customer Exists
	 * @return bool
	 */
	public function exists() {
		return boolval($this->queryCustid($this->custID)->count());
	}

	/**
	 * Return Payment for Customer ID
	 * @return ArCashHead
	 */
	public function header() {
		if ($this->exists($this->custID) === false) {
			return false;
		}
		return $this->queryCustid($this->custID)->findOne();
	}
}
