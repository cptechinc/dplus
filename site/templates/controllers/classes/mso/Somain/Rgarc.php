<?php namespace Controllers\Mso\Somain;
// ProcessWire 
use ProcessWire\WireData;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus
use Dplus\Filters;
use Dplus\Codes;


class Rgarc extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'rgarc';
	const TITLE      = 'RGA / Return Reason Code';
	const SUMMARY    = 'View / Edit RGA / Return Reason Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::rgarcUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Mso\SoReasonCode();
	}

	public static function getCodeTable() {
		return Codes\Mso\Rgarc::instance();
	}
}