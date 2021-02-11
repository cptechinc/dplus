<?php namespace Dplus\CodeValidators\Mar;

use Dplus\CodeValidators\Map as MapValidator;

/**
 * Mar
 * Class for Validating Spm fields
 */
class Spm extends Mar {
	public function vendorid($id) {
		$validator = new MapValidator();
		return $validator->vendorid($id);
	}
}
