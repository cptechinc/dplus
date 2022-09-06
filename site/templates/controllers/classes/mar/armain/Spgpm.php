<?php namespace Controllers\Mar\Armain;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes;


class Spgpm extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'ccm';
	const TITLE      = 'Salesperson Group Code';
	const SUMMARY    = 'View / Edit Salesperson Group Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::spgpmUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Mar\SalespersonGroupCode();
	}

	public static function getCodeTable() {
		return Codes\Mar\Spgpm::instance();
	}
}
