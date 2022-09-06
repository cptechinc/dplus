<?php namespace Controllers\Mar\Armain;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes;


class Cocom extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'cocom';
	const TITLE      = 'Country Code';
	const SUMMARY    = 'View / Edit Country Codes';
	const SHOWONPAGE = 25;

	public static function _url() {
		return Menu::cocomUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Mar\CountryCode();
	}

	public static function getCodeTable() {
		return Codes\Mar\Cocom::instance();
	}
}
