<?php namespace Controllers\Ajax\Lookup;
// Dplus Filters
use Dplus\Filters;

class Mar extends Lookup {
	/**
	 * Search Customers
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function customers($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		$page = self::pw('page');
		$filter = new Filters\Mar\Customer();
		$filter->user(self::pw('user'));
		$page->headline = "Customers";
		$data->addSortColumns = [\Customer::aliasproperty('id')];
		return self::filterResults($filter, $data);
	}

	/**
	 * Search Tax Codes
	 * @param  object $data
	 *                     q        Search Term
	 * @return void
	 */
	public static function taxCodes($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "Tax Codes";
		$filter = new Filters\Mar\ArTaxCode();
		return self::filterResults($filter, $data);
	}
}
