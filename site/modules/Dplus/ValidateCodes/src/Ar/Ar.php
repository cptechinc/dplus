<?php namespace Dplus\CodeValidators;

use ProcessWire\WireData;

use ShipviaQuery, Shipvia;
use CustomerQuery, Customer;

/**
 * Ar
 * Class for Validating AR table codes, IDs
 */
class Ar extends WireData {
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
}
