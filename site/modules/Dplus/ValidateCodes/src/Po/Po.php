<?php namespace Dplus\CodeValidators;

use ProcessWire\WireData;

use PurchaseOrderQuery, PurchaseOrder;
use ApInvoiceQuery, ApInvoice;

/**
 * Po
 * Class for Validating PO table codes, IDs
 */
class Po extends WireData {
	public function __construct() {
		$this->exists  = false;
		$this->ponbr  = '';
	}

	/**
	 * Returns if Purchase Order Number exists in the Purchase Order table
	 * @param  string $ponbr Purchase Order Number
	 * @return bool
	 */
	public function po($ponbr) {
		$q = PurchaseOrderQuery::create();
		$q->filterByPonbr($ponbr);
		return boolval(($q->count()));
	}

	/**
	 * Returns if Purchase Order Number exists in the Ap Invoice table
	 * @param  string $ponbr Purchase Order Number
	 * @return bool
	 */
	public function invoice($ponbr) {
		$q = ApInvoiceQuery::create();
		$q->filterByInvnbr($ponbr);
		return boolval(($q->count()));
	}
}
