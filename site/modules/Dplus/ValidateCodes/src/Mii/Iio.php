<?php namespace Dplus\CodeValidators\Mii;

use Dplus\CodeValidators\Mii;
use Dplus\CodeValidators\Min;

/**
 * Mii
 */
class Iio extends Mii {
	/**
	 * Validate Login ID
	 * @param  string $userID User ID
	 * @return bool
	 */
	public function userid($userID) {
		$validate = new MsaValidator();
		return $validate->userid($userID);
	}

	/**
	 * Validate Warehouse ID
	 * @param  string $whseID Warehouse ID
	 * @return bool
	 */
	public function whseid($whseID) {
		if ($whseID == '**') {
			return true;
		}
		$validate = new MinValidator();
		return $validate->whseid($whseID);
	}
}
