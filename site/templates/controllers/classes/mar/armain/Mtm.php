<?php namespace Controllers\Mar\Armain;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// ProcessWire
use ProcessWire\WireData;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes;


class Mtm extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'mtm';
	const TITLE      = 'Master Tax Code';
	const SUMMARY    = 'View / Edit Master Tax Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::mtmUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Mar\ArTaxCode();
	}

	public static function getCodeTable() {
		return Codes\Mar\Mtm::instance();
	}

/* =============================================================
	Render HTML / JS
============================================================= */
	protected static function renderList(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mar/mtm/list.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderModal(WireData $data) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mar/mtm/edit-modal.twig', ['manager' => $codeTable]);
	}
}
