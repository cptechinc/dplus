<?php namespace Dplus\Filters\Mar;
// Dplus Model
use CustomerQuery, Customer as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page, ProcessWire\User;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
 * Wrapper Class for adding Filters to the CustomerQuery class
 */
class Customer extends AbstractFilter {
	const MODEL = 'Customer';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$columns = [
			Model::aliasproperty('custid'),
			Model::aliasproperty('name'),
			Model::aliasproperty('address1'),
			Model::aliasproperty('address2'),
			Model::aliasproperty('city'),
			Model::aliasproperty('state'),
			Model::aliasproperty('zip'),
		];
		$this->query->search_filter($columns, strtoupper($q));
	}

/* =============================================================
	2. Base Filter Functions
============================================================= */
	public function active() {
		$config = ConfigCiQuery::create()->findOne();

		if ($config->show_inactive != ConfigCi::BOOL_TRUE) {
			$this->query->filterByActive(Model::STATUS_ACTIVE);
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
		return $this;
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

/* =============================================================
	3. Input Query Functions
============================================================= */

/* =============================================================
	4. Misc Query Functions
============================================================= */
	/**
	 * Return if Customer Exists
	 * @param  string $custID Customer ID
	 * @return bool
	 */
	public function exists($custID) {
		return boolval($this->getQueryClass()->filterByCustid($custID)->count());
	}

	/**
	 * Return Customer
	 * @param  string $custID Customer ID
	 * @return Customer
	 */
	public function getCustomer($custID) {
		return CustomerQuery::create()->findOneByCustid($custID);
	}

	/**
	 * Return Position of Record in results
	 * @param  string $custID Customer ID
	 * @return int
	 */
	public function positionById($custID) {
		if ($this->exists($custID) === false) {
			return 0;
		}
		$v = $this->getCustomer($custID);
		return $this->position($v);
	}
}
