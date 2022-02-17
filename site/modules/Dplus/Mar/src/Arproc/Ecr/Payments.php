<?php namespace Dplus\Mar\Arproc\Ecr;
// Dplus Model
use ArPaymentPendingQuery, ArPaymentPending;
use GlCodeQuery, GlCode;
// Dplus Codes
use Dplus\Codes\Mar\Trm;
// ProcessWire
use ProcessWire\WireData;

/**
 * Payments
 *
 * Handles CRUD for ArPaymentPendings
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
	 * @return ArPaymentPendingQuery
	 */
	public function query() {
		return ArPaymentPendingQuery::create();
	}

	/**
	 * Return Query filtered by Invoice Number
	 * @param  string $invnbr Invoice Number
	 * @return ArPaymentPendingQuery
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
	 * @return ArPaymentPending
	 */
	public function payment($invnbr) {
		if ($this->exists($invnbr) === false) {
			return false;
		}
		return $this->queryInvnbr($invnbr)->findOne();
	}

/* =============================================================
	Supplemental
============================================================= */
	/**
	 * Return Terms Code Description
	 * @param  string $id AR Terms Code
	 * @return string
	 */
	public function getTrmDescription($id) {
		return Trm::getInstance()->description($id);
	}

	/**
	 * Return GL Code Description
	 * @param  string $id GL Code
	 * @return string
	 */
	public function getGlCodeDescription($id) {
		$q = GlCodeQuery::create();
		$q->select(GlCode::aliasproperty('description'));
		$q->filterById($id);
		return $q->findOne();
	}

	/**
	 * [getTotalDue description]
	 * @return [type] [description]
	 */
	public function getTotalPaid($custID) {
		$col = ArPaymentPending::aliasproperty('amtpaid');
		$q = $this->query();
		$q->filterByCustid($custID);
		$q->withColumn("SUM($col)", 'total');
		$q->select('total');
		return floatval($q->findOne());
	}
}
