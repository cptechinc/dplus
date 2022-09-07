<?php namespace Controllers\Min\Inmain;
// ProcessWire 
use ProcessWire\WireData;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus
use Dplus\Filters;
use Dplus\Codes;

/**
 * Stcm
 * 
 * Controller for handling HTTP Requests for the Stcm Codetable
 */
class Stcm extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'stcm';
	const TITLE      = 'Stock Code';
	const SUMMARY    = 'View / Edit Stock Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::stcmUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Min\InvStockCode();
	}

	public static function getCodeTable() {
		return Codes\Min\Stcm::instance();
	}
}