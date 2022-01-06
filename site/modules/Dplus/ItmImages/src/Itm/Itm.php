<?php namespace Dplus\Urls\ItmImages\Itm;
// Dplus Model
use ItemMasterItemQuery as Query, ItemMasterItem;

/**
 * Itm
 * Handles Reading from Item Master
 */
class Itm {
	public static function exists($itemID) {
		return boolval(Query::create()->filterByItemid($itemID)->count());
	}
}
