<?php namespace Dplus\CodeValidators\Mki;

use ProcessWire\WireData;

use Propel\Runtime\ActiveQuery\Criteria;

use Dplus\CodeValidators\Min as MinValidator;

/**
 * Mki
 *
 * Class for Validating Mki Code Tables / IDs
 */
class Mki extends WireData {
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
}
