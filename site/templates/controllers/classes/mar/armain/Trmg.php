<?php namespace Controllers\Mar\Armain;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes;


class Trmg extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'trmg';
	const TITLE      = 'Terms Group Code';
	const SUMMARY    = 'View / Edit Terms Group Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::trmgUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Mar\ArTermsGroup();
	}

	public static function getCodeTable() {
		return Codes\Mar\Trmg::instance();
	}
}
