<?php namespace Controllers\Ajax\Lookup;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
use Propel\Runtime\ActiveQuery\ModelCriteria as BaseQuery;
// Dplus Filters
use Dplus\Filters;

class Min extends Lookup {
	/**
	 * Search Label Formats
	 * @param  object $data
	 *                     q        Search Term
	 * @return void
	 */
	public static function productLines($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "Product Lines";
		$filter = new Filters\Min\InvProductLineCode();
		return self::filterResults($filter, $data);
	}
}
