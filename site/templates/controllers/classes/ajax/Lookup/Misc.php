<?php namespace Controllers\Ajax\Lookup;
// Dplus Filters
use Dplus\Filters;

class Misc extends Lookup {
	/**
	 * Search State Codes
	 * @param  object $data
	 *                     q        Search Term
	 * @return void
	 */
	public static function stateCodes($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "State Codes";
		$filter = new Filters\Misc\StateCode();
		return self::filterResults($filter, $data);
	}

	/**
	 * Search Country Codes
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function countryCodes($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "Country Codes";
		$filter = new Filters\Misc\CountryCode();
		return self::filterResults($filter, $data);
	}
}
