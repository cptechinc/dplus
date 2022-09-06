<?php namespace Controllers\Mar\Armain;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes;


class Ccm extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'ccm';
	const TITLE      = 'Customer Commission Code';
	const SUMMARY    = 'View / Edit Customer Commission Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::ccmUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Mar\ArCommissionCode();
	}

	public static function getCodeTable() {
		return Codes\Mar\Ccm::instance();
	}
}
