<?php namespace Controllers\Mar\Armain;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes;


class Cpm extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'cpm';
	const TITLE      = 'Customer Price Code';
	const SUMMARY    = 'View / Edit Customer Price Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::cpmUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Mar\ArPriceCode();
	}

	public static function getCodeTable() {
		return Codes\Mar\Cpm::instance();
	}
}
