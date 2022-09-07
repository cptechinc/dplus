<?php namespace Controllers\Min\Inmain;
// Dplus
use Dplus\Filters;
use Dplus\Codes;

/**
 * Igpm
 * 
 * Controller for handling HTTP Requests for the Igpm Codetable
 */
class Igpm extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'igpm';
	const TITLE      = 'Inventory Price Code';
	const SUMMARY    = 'View / Edit Inventory Price Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::igpmUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Min\InvPriceCode();
	}

	public static function getCodeTable() {
		return Codes\Min\Igpm::instance();
	}
}