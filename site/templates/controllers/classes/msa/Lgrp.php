<?php namespace Controllers\Msa;
// Dplus Filters
use Dplus\Filters;
// Dplus Codes
use Dplus\Codes;

class Lgrp extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'lgrp';
	const TITLE = 'Login Group Entry';
	const SUMMARY = 'View / Edit Login Groups';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::lgrpUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Msa\SysLoginGroup();
	}

	public static function getCodeTable() {
		return Codes\Msa\Lgrp::instance();
	}
}
