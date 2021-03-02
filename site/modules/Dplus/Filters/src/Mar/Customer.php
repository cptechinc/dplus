<?php namespace Dplus\Filters\Mar;

use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page, ProcessWire\User;
use Dplus\Filters\AbstractFilter;

use CustomerQuery, Customer as CustomerClass;

class Customer extends AbstractFilter {
	const MODEL = 'Customer';

/* =============================================================
	Abstract Contract Functions
============================================================= */
	public function initQuery() {
		$this->query = CustomerQuery::create();
	}

	public function _search($q) {
		$columns = [
			CustomerClass::get_aliasproperty('custid'),
			CustomerClass::get_aliasproperty('name'),
			CustomerClass::get_aliasproperty('address1'),
			CustomerClass::get_aliasproperty('address2'),
			CustomerClass::get_aliasproperty('city'),
			CustomerClass::get_aliasproperty('state'),
			CustomerClass::get_aliasproperty('zip'),
		];
		$this->query->search_filter($columns, strtoupper($q));
	}

/* =============================================================
	Misc Query Functions
============================================================= */
	/**
	 * Return Position of Customer ID in Results
	 * @param  Vendor $custID  Customer ID
	 * @return int
	 */
	public function position($custID) {
		if (!$this->exists($custID)) {
			return 0;
		}
		$customers = $this->query->find();
		$customer = $this->get_customer($custID);
		return $customers->search($customer);
	}
	
	/**
	 * Return if Customer Exists
	 * @param  string $custID Customer ID
	 * @return bool
	 */
	public function exists($custID) {
		return boolval(CustomerQuery::create()->filterByCustid($custID)->count());
	}

/* =============================================================
	Base Filter Functions
============================================================= */
	public function active() {
		$config = ConfigCiQuery::create()->findOne();

		if ($config->show_inactive != ConfigCi::BOOL_TRUE) {
			$this->query->filterByActive(CustomerClass::STATUS_ACTIVE);
		}
		return $this;
	}

	/**
	 * Filter Query by Cust ID
	 * @param  string|array $custID Cust ID
	 * @return void
	 */
	public function custid($custID) {
		$this->query->filterByCustid($custID);
		return $thius;
	}

	/**
	 * Filter User's Customer if Sales Rep
	 * @param  User   $user [description]
	 * @return self;
	 */
	public function user(User $user) {
		if ($user->is_salesrep()) {
			$this->query->filterByCustid($user->get_customers(), Criteria::IN);
		}
		return $this;
	}
}
