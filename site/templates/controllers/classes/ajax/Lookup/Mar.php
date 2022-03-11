<?php namespace Controllers\Ajax\Lookup;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
use Propel\Runtime\ActiveQuery\ModelCriteria as BaseQuery;
// Dplus Filters
use Dplus\Filters;

class Mar extends Lookup {
	/**
	 * Search Label Formats
	 * @param  object $data
	 *                     q        Search Term
	 * @return void
	 */
	public static function salespersonGroupCodes($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "Salesperson Group Codes";
		$filter = new Filters\Mar\SalespersonGroupCode();
		return self::filterResults($filter, $data);
	}
}
