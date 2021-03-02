<?php namespace Dplus\CodeValidators;

use ProcessWire\WireData;

use GlCodeQuery, GlCode;


/**
 * Mgl
 * Class for Validating Mgl table codes, IDs
 */
class Mgl extends WireData {
	/**
	 * Validate General Ledger Code Exists
	 * @param  string $code GL Code
	 * @return bool
	 */
	public function glCode($code) {
		$q = GlCodeQuery::create();
		$q->filterByCode($code);
		return boolval($q->count());
	}
}
