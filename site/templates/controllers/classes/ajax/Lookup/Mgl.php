<?php namespace Controllers\Ajax\Lookup;
// Dplus Filters
use Dplus\Filters;

class Mgl extends Lookup {
	/**
	 * Filter General Ledger Codes
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function generalLedgerCodes($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "General Ledger Accounts";
		$filter = new Filters\Mgl\GlCode();
		return self::filterResults($filter, $data);
	}
}
