<?php namespace Dplus\CodeValidators\Mar;

use Dplus\CodeValidators\Mar;

use Dplus\CodeValidators\Map as MapValidator;
use Dplus\CodeValidators\Msa as MsaValidator;

/**
 * Mar
 * Class for Validating Spm fields
 */
class Spm extends Mar {
	public function vendorid($id) {
		$validator = new MapValidator();
		return $validator->vendorid($id);
	}

	public function userid($id) {
		$validate = new MsaValidator();
		return $validate->userid($id);
	}
}
