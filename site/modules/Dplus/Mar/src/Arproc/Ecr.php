<?php namespace Dplus\Mar\Arproc;
// Propel ORM Library
use Propel\Runtime\ActiveQuery\Criteria;
// ProcessWire
use ProcessWire\WireData;

/**
 * Ecr
 *
 * @property Ecr\Payments Payments
 */
class Ecr extends WireData {
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
		$this->user     = $this->wire('user');

		$this->invoices = Ecr\Invoices::instance($this->custID);
		$this->payments = Ecr\Payments::instance();
		$this->header   = Ecr\Header::instance($this->custID);
		$this->totals   = Ecr\Totals::instance($this->custID);
		$this->session  = Ecr\Session::session();
	}

	public function headerisLocked() {
		$q = $this->header->queryCustid($this->custID);
		$q->filterByClerkid($this->user->loginid, Criteria::NOT_EQUAL);
		$locked = boolval($q->count());

		return $this->header->exists() && $locked === true;
	}
}
