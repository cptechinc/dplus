<?php namespace Controllers\Ajax\Lookup;
// Dplus Filters
use Dplus\Filters;

class Mso extends Lookup {
	/**
	 * Search Freight Codes
	 * @param  object $data
	 *                     q        Search Term
	 * @return string
	 */
	public static function freightCodes($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "Freight Codes";
		$filter = new Filters\Mso\MotorFreightCode();
		return self::filterResults($filter, $data);
	}
}
