<?php namespace Controllers\Mar\Armain;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes;


class Worm extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'worm';
	const TITLE      = 'Write-Off Reason Code';
	const SUMMARY    = 'View / Edit Write-Off Reason Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::wormUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Mar\ArWriteOffCode();
	}

	public static function getCodeTable() {
		return Codes\Mar\Worm::instance();
	}
}
