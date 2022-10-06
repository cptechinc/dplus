<?php namespace Controllers\Mar\Armain;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes;


class Crtm extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'crtm';
	const TITLE      = 'Customer Route Code';
	const SUMMARY    = 'View / Edit Customer Route Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::crtmUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Mar\ArRouteCode();
	}

	public static function getCodeTable() {
		return Codes\Mar\Crtm::instance();
	}
}
