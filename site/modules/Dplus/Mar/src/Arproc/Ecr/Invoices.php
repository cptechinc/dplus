<?php namespace Dplus\Mar\Arproc\Ecr;
// Dplus Model
use ArInvoiceQuery, ArInvoice;
// ProcessWire
use ProcessWire\WireData;

/**
 * Invoices
 */
class Invoices extends WireData {
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
		$this->custID = $custID;
	}

	/**
	 * Return Query
	 * @return ArInvoiceQuery
	 */
	public function query() {
		return ArInvoiceQuery::create();
	}

	/**
	 * Return Query filtered by Customer ID
	 * @param  string $custID Customer ID
	 * @return ArInvoiceQuery
	 */
	public function queryCustid($custID) {
		return $this->query()->filterByCustid($custID);
	}

	/**
	 * [getTotalDue description]
	 * @return [type] [description]
	 */
	public function getTotalDue() {
		$col = ArInvoice::aliasproperty('total');
		$q = $this->queryCustid($this->custID);
		$q->withColumn("SUM($col)", 'total');
		$q->select('total');
		return floatval($q->findOne());
	}
}
