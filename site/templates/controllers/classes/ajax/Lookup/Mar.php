<?php namespace Controllers\Ajax\Lookup;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
use Propel\Runtime\ActiveQuery\ModelCriteria as BaseQuery;
// Dplus Filters
use Dplus\Filters;

class Mar extends Lookup {
	/**
	 * Search Salesperson Group Codes
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

	/**
	 * Search Salesperson Codes
	 * @param  object $data
	 *                     q        Search Term
	 * @return void
	 */
	public static function salespersonCodes($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "Salesperson Codes";
		$filter = new Filters\Mar\SalesPerson();
		return self::filterResults($filter, $data);
	}

	/**
	 * Search AR Tax Codes
	 * @param  object $data
	 *                     q        Search Term
	 * @return void
	 */
	public static function arTaxCodes($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "Tax Codes";
		$filter = new Filters\Mar\ArTaxCode();
		return self::filterResults($filter, $data);
	}
}
