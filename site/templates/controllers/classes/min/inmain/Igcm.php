<?php namespace Controllers\Min\Inmain;
// Dplus
use Dplus\Filters;
use Dplus\Codes;

class Igcm extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'igcm';
	const TITLE      = 'Inventory Commission';
	const SUMMARY    = 'View / Edit Inventory Commission Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::igcmUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Min\InvCommissionCode();
	}

	public static function getCodeTable() {
		return Codes\Min\Igcm::instance();
	}
}