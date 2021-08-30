<?php namespace Dplus\CodeValidators;
// Dplus Models
use PurchaseOrderQuery, PurchaseOrder;
use ApInvoiceQuery, ApInvoice;
use PoConfirmCodeQuery, PoConfirmCode;
// ProcessWire
use ProcessWire\WireData;

/**
 * Po
 * Class for Validating PO table codes, IDs
 */
class Mpo extends WireData {
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

	/**
	 * Returns if Cnfm Code Exists
	 * @param  string $code Cnfm Code
	 * @return bool
	 */
	public function cnfm($code) {
		$q = PoConfirmCodeQuery::create();
		$q->filterById($code);
		return boolval(($q->count()));
	}
}
