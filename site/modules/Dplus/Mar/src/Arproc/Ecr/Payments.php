<?php namespace Dplus\Mar\Arproc\Ecr;
// Dplus Model
use ArPaymentQuery, ArPayment;
// ProcessWire
use ProcessWire\WireData;

/**
 * Payments
 *
 * Handles CRUD for ArPayments
 */
class Payments extends WireData {
	private static $instance;

	/**
	 * Return Instance
	 * @return self
	 */
	public static function instance() {
		if (empty(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Return Query
	 * @return ArPaymentQuery
	 */
	public function query() {
		return ArPaymentQuery::create();
	}

	/**
	 * Return Query filtered by Invoice Number
	 * @param  string $invnbr Invoice Number
	 * @return ArPaymentQuery
	 */
	public function queryInvnbr($invnbr) {
		return $this->query()->filterByInvoicenbr($invnbr);
	}

	/**
	 * Return if Payment for Invoice Exists
	 * @param  string $invnbr Invoice Number
	 * @return bool
	 */
	public function exists($invnbr) {
		return boolval($this->queryInvnbr($invnbr)->count());
	}

	/**
	 * Return Payment for Invoice Number
	 * @param  string $invnbr Invoice Number
	 * @return ArPayment
	 */
	public function payment($invnbr) {
		if ($this->exists($invnbr) === false) {
			return false;
		}
		return $this->queryInvnbr($invnbr)->findOne();
	}
}
