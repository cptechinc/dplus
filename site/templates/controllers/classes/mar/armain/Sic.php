<?php namespace Controllers\Mar\Armain;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes;


class Sic extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'sic';
	const TITLE      = 'Standard Industrial Class';
	const SUMMARY    = 'View / Edit AR Standard Industrial Classes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::sicUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Mar\ArStandardIndustrialClass();
	}

	public static function getCodeTable() {
		return Codes\Mar\Sic::instance();
	}
}
