<?php namespace Controllers\Mar\Armain;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// ProcessWire
use ProcessWire\WireData;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes;


class Spm extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'spm';
	const TITLE      = 'Salesperson';
	const SUMMARY    = 'View / Edit Salespersons';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::spmUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Mar\SalesPerson();
	}

	public static function getCodeTable() {
		return Codes\Mar\Spm::instance();
	}

/* =============================================================
	Render HTML / JS
============================================================= */
	protected static function renderList(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mar/spm/list.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderModal(WireData $data) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mar/spm/edit-modal.twig', ['manager' => $codeTable]);
	}
}
