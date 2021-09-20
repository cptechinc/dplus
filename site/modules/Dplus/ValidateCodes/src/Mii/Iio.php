<?php namespace Dplus\CodeValidators\Mii;

use Dplus\CodeValidators as Validators;
use Dplus\CodeValidators\Mii;
use Dplus\CodeValidators\Min;

/**
 * IIo
 */
class Iio extends Mii {
	/**
	 * Validate Login ID
	 * @param  string $userID User ID
	 * @return bool
	 */
	public function userid($userID) {
		$validate = new Validators\Msa();
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
		$validate = new Validators\Min();
		return $validate->whseid($whseID);
	}
}
