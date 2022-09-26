<?php namespace Controllers\Map\Apmain;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// ProcessWire
use ProcessWire\WireData;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes;


class Cocom extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'cocom';
	const TITLE      = 'Country Code';
	const SUMMARY    = 'View / Edit Country Codes';
	const SHOWONPAGE = 25;

	public static function _url() {
		return Menu::cocomUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Mar\CountryCode();
	}

	public static function getCodeTable() {
		return Codes\Mar\Cocom::instance();
	}
	
/* =============================================================
	Render HTML / JS
============================================================= */
	protected static function renderList(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mar/cocom/list.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderModal(WireData $data) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mar/cocom/edit-modal.twig', ['manager' => $codeTable]);
	}

	protected static function renderJs(WireData $data) {
		return self::pw('config')->twig->render("code-tables/mar/cocom/.js.twig", ['cocom' => static::getCodeTable()]);
	}
}
