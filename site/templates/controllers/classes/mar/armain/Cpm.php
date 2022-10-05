<?php namespace Controllers\Mar\Armain;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// ProcessWire
use ProcessWire\WireData;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes;


class Cpm extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'cpm';
	const TITLE      = 'Customer Price Code';
	const SUMMARY    = 'View / Edit Customer Price Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::cpmUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Mar\ArPriceCode();
	}

	public static function getCodeTable() {
		return Codes\Mar\Cpm::instance();
	}

/* =============================================================
	Render HTML / JS
============================================================= */
	protected static function renderList(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mar/cpm/list.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderModal(WireData $data) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mar/cpm/edit-modal.twig', ['manager' => $codeTable]);
	}
}
