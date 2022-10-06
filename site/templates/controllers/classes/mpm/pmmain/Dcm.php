<?php namespace Controllers\Mpm\Pmmain;
// ProcessWire 
use ProcessWire\WireData;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus
use Dplus\Filters;
use Dplus\Codes;

/**
 * Dcm
 * 
 * Controller for handling HTTP Requests for the Dcm Codetable
 */
class Dcm extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'dcm';
	const TITLE      = 'Work Center';
	const SUMMARY    = 'View / Edit Work Center Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::dcmUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Mpm\PrWorkCenter();
	}

	public static function getCodeTable() {
		return Codes\Mpm\Dcm::instance();
	}
}