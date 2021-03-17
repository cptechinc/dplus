<?php namespace Dplus\CodeValidators;

use ProcessWire\WireData, ProcessWire\User;

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
		$q->filterByOrdernumber($this->wire('sanitizer')->ordn($ordn));
		return boolval($q->count());
	}

	/**
	 * Return If User has access to Sales Order
	 * @param  string $ordn Order #
	 * @param  User   $user Check if User is Sales Rep
	 * @return bool
	 */
	public function orderUser($ordn, User $user) {
		if ($user->hasRole('slsrep') === false) {
			return true;
		}
		$q = SalesOrderQuery::create();
		$q->filterByOrdernumber($this->wire('sanitizer')->ordn($ordn));
		$q->filterBySalesPerson($user->repid);
		return boolval($q->count());
	}

	/**
	 * Returns if Order # exists in the Sales History table
	 * @param  string $ordn Order #
	 * @return bool
	 */
	public function invoice($ordn) {
		$q = SalesHistoryQuery::create();
		$q->filterByOrdernumber($this->wire('sanitizer')->ordn($ordn));
		return boolval($q->count());
	}

	/**
	 * Return If User has access to Sales Order
	 * @param  string $ordn Order #
	 * @param  User   $user Check if User is Sales Rep
	 * @return bool
	 */
	public function invoiceUser($ordn, User $user) {
		if ($user->hasRole('slsrep') === false) {
			return true;
		}
		$q = SalesHistoryQuery::create();
		$q->filterByOrdernumber($this->wire('sanitizer')->ordn($ordn));
		$q->filterBySalesPerson($user->repid);
		return boolval($q->count());
	}

	public function orderAccess($ordn, User $user) {
		if ($this->order($ordn)) {
			return $this->orderUser($ordn, $user);
		}
		if ($this->invoice($ordn)) {
			return $this->InvoiceUser($ordn, $user);
		}
		return false;
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
