<?php namespace Controllers\Min\Inmain;
// Dplus
use Dplus\Filters;
use Dplus\Codes;

/**
 * Iplm
 * 
 * Controller for handling HTTP Requests for the Iplm Codetable
 */
class Iplm extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'iplm';
	const TITLE      = 'Inventory Product Line Code';
	const SUMMARY    = 'View / Edit Inventory Product Line Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::iplmUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Min\InvProductLineCode();
	}

	public static function getCodeTable() {
		return Codes\Min\Iplm::instance();
	}
}