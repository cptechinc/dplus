<?php namespace Controllers\Min\Inmain;
// Dplus
use Dplus\Filters;
use Dplus\Codes;

class Csccm extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'csccm';
	const TITLE      = 'Customer Stocking Cell Code';
	const SUMMARY    = 'View / Edit Customer Stocking Cell Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::csccmUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Min\CustomerStockingCell();
	}

	public static function getCodeTable() {
		return Codes\Min\Csccm::instance();
	}
}