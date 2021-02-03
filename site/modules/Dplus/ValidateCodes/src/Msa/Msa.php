<?php namespace Dplus\CodeValidators;

use ProcessWire\WireData;

use DplusUserQuery, DplusUser;


/**
 * Msa
 * Class for Validating MSA table codes, IDs
 */
class Msa extends WireData {
	/**
	 * Validate Login ID
	 * @param  string $loginID Login ID
	 * @return bool
	 */
	public function userid($loginID) {
		$q = DplusUserQuery::create();
		$q->filterByUserid($loginID);
		return boolval($q->count());
	}
}
