<?php namespace Controllers\Mar\Armain;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes;


class Cuc extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'cuc';
	const TITLE      = 'Customer User Code';
	const SUMMARY    = 'View / Edit Customer User Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::cucUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Mar\ArCustUserCode();
	}

	public static function getCodeTable() {
		return Codes\Mar\Cuc::instance();
	}
}
