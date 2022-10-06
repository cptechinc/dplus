<?php namespace Controllers\Mar\Armain;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes;


class Suc extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'suc';
	const TITLE      = 'Ship-To User Code';
	const SUMMARY    = 'View / Edit Ship-To User Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::sucUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Mar\ArShiptoUserCode();
	}

	public static function getCodeTable() {
		return Codes\Mar\Suc::instance();
	}
}
