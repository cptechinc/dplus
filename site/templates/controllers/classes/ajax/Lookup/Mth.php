<?php namespace Controllers\Ajax\Lookup;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
use Propel\Runtime\ActiveQuery\ModelCriteria as BaseQuery;
// Dplus Filters
use Dplus\Filters;

class Mth extends Lookup {
	/**
	 * Search Label Formats
	 * @param  object $data
	 *                     q        Search Term
	 * @return void
	 */
	public static function labels($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "Label Formats";
		$filter = new Filters\Mth\ThermalLabelFormat();
		return self::filterResults($filter, $data);
	}
}
