<?php namespace Dplus\CodeValidators;

use ProcessWire\WireData;

use SalesOrderQuery, SalesOrder;
use SalesHistoryQuery, SalesHistory;

/**
 * Mso
 * Class for Validating SO table codes, IDs
 */
class Mso extends WireData {
	/**
	 * Returns if Order # exists in the Sales Order table
	 * @param  string $ordn Order #
	 * @return bool
	 */
	public function order($ordn) {
		$q = SalesOrderQuery::create();
		$q->filterByOrdernumber($ordn);
		return boolval($q->count());
	}

	/**
	 * Returns if Order # exists in the Sales History table
	 * @param  string $ordn Order #
	 * @return bool
	 */
	public function invoice($ordn) {
		$q = SalesHistoryQuery::create();
		$q->filterByOrdernumber($ordn);
		return boolval($q->count());
	}

	/**
	 * Return if Motor Freight Code is valid
	 * @param  string $code  Motor Freight Code
	 * @return bool
	 */
	public function freightcode($code) {
		$stcm = $this->modules->get('CodeTablesMfcm');
		return $stcm->code_exists($code);
	}
}
