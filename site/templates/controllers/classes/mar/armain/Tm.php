<?php namespace Controllers\Mar\Armain;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// ProcessWire
use ProcessWire\WireData;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes;


class Tm extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'tm';
	const TITLE      = 'Customer Tax Code';
	const SUMMARY    = 'View / Edit Customer Tax Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::tmUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Mar\ArCustTaxCode();
	}

	public static function getCodeTable() {
		return Codes\Mar\Tm::instance();
	}

/* =============================================================
	Render HTML / JS
============================================================= */
	protected static function renderList(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/list.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderListForPrinting(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mar/tm/list-print.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderModal(WireData $data) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mar/tm/edit-modal.twig', ['manager' => $codeTable]);
	}
}
