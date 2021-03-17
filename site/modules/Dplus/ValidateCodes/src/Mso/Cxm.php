<?php namespace Dplus\CodeValidators\Mso;

use ItemXrefCustomerQuery, ItemXrefCustomer;

use ProcessWire\WireData;

use Dplus\CodeValidators\Mar as MarValidator;
use Dplus\CodeValidators\Min as MinValidator;

/**
 * Cxm
 * Class for Validating CXM fields
 */
class Cxm extends WireData {
/* =============================================================
	CXM Functions
============================================================= */
	public function rounding_option($option) {
		return in_array($option, ItemXrefCustomer::ROUNDING_OPTIONS);
	}

	/**
	 * Validate if VXM X-ref exists
	 * @param  string $custID     Customer ID
	 * @param  string $custitemID Customer Item ID
	 * @return bool
	 */
	public function exists($custID, $custitemID) {
		return $this->modules->get('XrefCxm')->xref_exists($custID, $custitemID);
	}

/* =============================================================
	AR Functions
============================================================= */
	/**
	 * Return if Cust ID is Valid
	 * @param  string $custID Customer ID
	 * @return bool
	 */
	public function custid($custID) {
		$validate = new MarValidator();
		return $validate->custid($custID);
	}

/* =============================================================
	IN Functions
============================================================= */
	/**
	 * Return if Item ID is Valid
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function itemid($itemID) {
		$validate = new MinValidator();
		return $validate->itemid($itemID);
	}

	/**
	 * Return if Unit of Measure Purchase Code is valid
	 * @param  string $code Unit of Measure Sale Code
	 * @return bool
	 */
	public function unitofm_sale($code) {
		$validate = new MinValidator();
		return $validate->unitofm_sale($code);
	}
}
