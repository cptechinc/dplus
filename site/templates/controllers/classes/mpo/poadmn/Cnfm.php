<?php namespace Controllers\Mpo\Poadmn;
// ProcessWire 
use ProcessWire\WireData;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus
use Dplus\Filters;
use Dplus\Codes;

/**
 * Cnfm
 * 
 * Controller for handling HTTP Requests for the Cnfm Codetable
 */
class Cnfm extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'cnfm';
	const TITLE      = 'Confirmation Code';
	const SUMMARY    = 'View / Edit Confirmation Code Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::cnfmUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Mpo\PoConfirmCode();
	}

	public static function getCodeTable() {
		return Codes\Mpo\Cnfm::instance();
	}
}