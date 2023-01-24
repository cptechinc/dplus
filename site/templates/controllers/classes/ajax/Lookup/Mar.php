<?php namespace Controllers\Ajax\Lookup;
// Dplus Filters
use Dplus\Filters;

class Mar extends Lookup {
	/**
	 * Search Credit Card Codes
	 * @param  object $data
	 *                     q        Search Term
	 * @return void
	 */
	public static function creditCardCodes($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "Credit Card Codes";
		$filter = new Filters\Mar\ArCreditCardCode();
		return self::filterResults($filter, $data);
	}

	/**
	 * Search Tax Codes
	 * @param  object $data
	 *                     q        Search Term
	 * @return void
	 */
	public static function arCustTaxCodes($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "Customer Tax Codes";
		$filter = new Filters\Mar\ArCustTaxCode();
		return self::filterResults($filter, $data);
	}

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
	 * Search SalesPerson Groups
	 * Search Tax Codes
	 * @param  object $data
	 *                     q        Search Term
	 * @return void
	 */
	public static function salesPersonGroups($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "Salesperson Groups";
		$filter = new Filters\Mar\SalespersonGroupCode();
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
	
	/**
	 * Search Terms Groups
	 * @param  object $data
	 *                     q        Search Term
	 * @return void
	 */
	public static function termsGroups($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "Terms Group Codes";
		$filter = new Filters\Mar\ArTermsGroup();
		return self::filterResults($filter, $data);
	}
}
