<?php namespace Dplus\Urls\ItmImages\Itm;
// Dplus Models
use InvOptCodeQuery as Query, InvOptCode;

/**
 * Optional Codes
 * Handles Reading from inv_opt_code table
 */
class OptionalCodes {
	/**
	 * Return Query
	 * @return Query
	 */
	public static function query() {
		return Query::create();
	}

	/**
	 * Return Query filtered by Item ID
	 * @param  string $itemID Item ID
	 * @return Query
	 */
	public static function queryItemid($itemID) {
		$q = self::query();
		$q->filterByItemid($itemID);
		return $q;
	}

	/**
	 * Return if Item ID exists in table
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public static function exists($itemID) {
		return boolval(self::queryItemid($itemID)->count());
	}

	/**
	 * Return Code Value for Item ID
	 * @param  string $itemID     Item ID
	 * @param  string $sysopcode  Sysop Code
	 * @return string
	 */
	public static function code($itemID, $sysopcode) {
		$q = self::queryItemid($itemID);
		$q->select(InvOptCode::aliasproperty('code'));
		$q->filterBySysop($sysopcode);
		return $q->findOne();
	}
}
