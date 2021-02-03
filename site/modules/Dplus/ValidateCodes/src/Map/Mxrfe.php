<?php namespace Dplus\CodeValidators\Map;

use Dplus\CodeValidators\Map as MapValidator;
use Dplus\CodeValidators\Min as MinValidator;

/**
 * ValidateMxrfe
 *
 * Class for validating MXRFE fields
 */
class Mxrfe extends MapValidator {
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
	public function unitofm($code) {
		$validate = new MinValidator();
		return $validate->unitofm_purchase($code);
	}

/* =============================================================
	MXRFE Functions
============================================================= */
	/**
	 * Validate if MXRFE X-ref exists
	 * @param  string $mnfrID     Vendor ID
	 * @param  string $mnfritemID Vendor Item ID
	 * @param  string $itemID       ITM Item ID
	 * @return bool
	 */
	public function exists($mnfrID, $mnfritemID, $itemID) {
		return $this->modules->get('XrefMxrfe')->xref_exists($mnfrID, $mnfritemID, $itemID);
	}
}
