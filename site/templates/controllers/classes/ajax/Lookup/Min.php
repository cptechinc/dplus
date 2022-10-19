<?php namespace Controllers\Ajax\Lookup;
// Dplus Filters
use Dplus\Filters;

class Min extends Lookup {
	

	/**
	 * Search Item Groups
	 * @param  object $data
	 *                     q        Search Term
	 * @return string
	 */
	public static function itemGroups($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "Item Groups";
		$filter = new Filters\Min\InvGroupCode();
		return self::filterResults($filter, $data);
	}

	/**
	 * Search Items
	 * @param  object $data
	 *                     q   Search Term
	 * @return void
	 */
	public static function itmItems($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "Item Master";
		$filter = new Filters\Min\ItemMaster();
		return self::filterResults($filter, $data);
	}

	/**
	 * Search MSDS Codes
	 * @param  object $data
	 *                     q        Search Term
	 * @return string
	 */
	public static function msdsCodes($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "MSDS Codes";
		$filter = new Filters\Min\MsdsCode();
		return self::filterResults($filter, $data);
	}

	/**
	 * Search Product Lines
	 * @param  object $data
	 *                     q        Search Term
	 * @return string
	 */
	public static function productLines($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "Product Lines";
		$filter = new Filters\Min\InvProductLineCode();
		return self::filterResults($filter, $data);
	}

	/**
	 * Search Tariff Codes
	 * @param  object $data
	 *                     q        Search Term
	 * @return string
	 */
	public static function tariffCodes($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "Tariff Codes";
		$filter = new Filters\Min\TariffCode();
		return self::filterResults($filter, $data);
	}

	/**
	 * Search Warehouses
	 * @param  object $data
	 *                     q        Search Term
	 * @return string
	 */
	public static function warehouses($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::pw('page')->headline = "Warehouses";
		$filter = new Filters\Min\Warehouse();
		return self::filterResults($filter, $data);
	}

	/**
	 * Search Warehouse Bins
	 * @param  object $data
	 *                     whseID Warehouse ID
	 *                     q        Search Term
	 * @return void
	 */
	public static function warehouseBins($data) {
		self::sanitizeParametersShort($data, self::FIELDS_LOOKUP);
		self::sanitizeParametersShort($data, ['whseID|text']);
		self::pw('page')->headline = "Warehouse Bins";
		$filter = new Filters\Min\WarehouseBin();
		return self::filterResults($filter, $data);
	}
}
