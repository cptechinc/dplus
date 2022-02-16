<?php namespace Dplus\Mar\Arproc\Ecr;
// Dplus Model
use ArCashHeadQuery, ArCashHead;
// ProcessWire
use ProcessWire\WireData;
use ProcessWire\User;

/**
 * Toals
 *
 * Holds ECR Totals Data
 *
 * @property string $custID Customer ID
 * @property float  $journal
 * @property float  $customer Customer's Total
 * @property float  $clerk    Clerk's Total
 */
class Totals extends WireData {
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
		$this->custID   = $custID;
		$this->journal  = 0;
		$this->customer = $this->getCustidTotal();
		$this->clerk    = $this->getClerkTotal();
	}

	/**
	 * Return Clerk's Received Amount Total
	 * @param  User|null $user
	 * @return float
	 */
	public function getClerkTotal(User $user = null) {
		$user = empty($user) === false ? $user : $this->wire('user');

		$col = ArCashHead::aliasproperty('amount');
		$q = Header::instance()->query();
		$q->withColumn("SUM($col)", 'amt');
		$q->select('amt');
		$q->filterByClerkid($user->loginid);
		return floatval($q->findOne());
	}

	/**
	 * Return Customer's Received Amount Total
	 * @return float
	 */
	public function getCustidTotal() {
		$col = ArCashHead::aliasproperty('amount');
		$q = Header::instance()->query();
		$q->withColumn("SUM($col)", 'amt');
		$q->select('amt');
		$q->filterByCustid($this->custID);
		return floatval($q->findOne());
	}

}
