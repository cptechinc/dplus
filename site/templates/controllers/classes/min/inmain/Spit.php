<?php namespace Controllers\Min\Inmain;
// Dplus
use Dplus\Filters;
use Dplus\Codes;

/**
 * Spit
 * 
 * Controller for handling HTTP Requests for the Spit Codetable
 */
class Spit extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'spit';
	const TITLE      = 'Special Item Code';
	const SUMMARY    = 'View / Edit Special Item Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::spitUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Min\InvSpecialCode();
	}

	public static function getCodeTable() {
		return Codes\Min\Spit::instance();
	}
}