<?php namespace Dplus\CodeValidators;

use ProcessWire\WireData;

use SalesOrderQuery, SalesOrder;
use SalesHistoryQuery, SalesHistory;

/**
 * So
 * Class for Validating SO table codes, IDs
 */
class So extends WireData {
	/**
	 * Returns if Order # exists in the Sales Order table
	 * @param  string $ordn Order #
	 * @return bool
	 */
	public function order($ordn) {
		$q = SalesOrderQuery::create();
		$q->filterByOrdernumber($ordn);
		return boolval(($q->count()));
	}

	/**
	 * Returns if Order # exists in the Sales History table
	 * @param  string $ordn Order #
	 * @return bool
	 */
	public function invoice($ordn) {
		$q = SalesHistoryQuery::create();
		$q->filterByOrdernumber($ordn);
		return boolval(($q->count()));
	}
}
