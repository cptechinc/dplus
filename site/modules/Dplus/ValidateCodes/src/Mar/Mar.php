<?php namespace Dplus\CodeValidators;

use ProcessWire\WireData;

use ShipviaQuery, Shipvia;
use CustomerQuery, Customer;
use SalesPersonQuery, SalesPerson;
use SalespersonGroupCodeQuery, SalespersonGroupCode;

/**
 * Mar
 * Class for Validating AR table codes, IDs
 */
class Mar extends WireData {
	/**
	 * Return true if Customer ID exists Exists
	 * @param  string $custID Customer ID
	 * @return bool
	 */
	public function custid($custID) {
		$q = CustomerQuery::create();
		$q->filterByCustid($custID);
		return boolval(($q->count()));
	}

	/**
	 * Return if Ship-Via Code Exists
	 * @param  string $code Ship-Via Code
	 * @return bool
	 */
	public function shipvia($code) {
		$q = ShipviaQuery::create();
		$q->filterByCode($code);
		return boolval(($q->count()));
	}

	/**
	 * Validate Sales Person ID exists
	 * @param  string $id Sales Person ID
	 * @return bool
	 */
	public function salespersonid($id) {
		$q = SalesPersonQuery::create();
		$q->filterById($id);
		return boolval(($q->count()));
	}

	/**
	 * Validate Sales Group ID exists
	 * @param  string $id Sales Group ID
	 * @return bool
	 */
	public function salesgroupid($id) {
		$q = SalespersonGroupCodeQuery::create();
		$q->filterById($id);
		return boolval(($q->count()));
	}
}
