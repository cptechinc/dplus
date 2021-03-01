<?php namespace Dplus\CodeValidators\Mpo;

use ProcessWire\WireData;


use Dplus\CodeValidators\Min as MinValidator;
use Dplus\CodeValidators\Mgl as MglValidator;

use PurchaseOrderQuery, PurchaseOrder;
use ApInvoiceQuery, ApInvoice;

/**
 * PoDetail
 * Class for Validating PO Detail column table codes, IDs
 */
class PoDetail extends WireData {
	/**
	 * Validate Item ID
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function itemid($itemID) {
		$validate = new MinValidator();
		return $validate->itemid($itemID);
	}

	/**
	 * Validate Warehouse ID
	 * @param  string $id Warehouse ID
	 * @return bool
	 */
	public function whseid($id) {
		$validate = new MinValidator();
		return $validate->whseid($id);
	}

	/**
	 * Validate General Ledger Code
	 * @param  string $code General Ledger Code
	 * @return bool
	 */
	public function glCode($code) {
		$validate = new MglValidator();
		return $validate->glCode($id);
	}
}
