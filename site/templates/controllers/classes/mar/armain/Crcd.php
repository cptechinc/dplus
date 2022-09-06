<?php namespace Controllers\Mar\Armain;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// ProcessWire
use ProcessWire\WireData;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes;


class Crcd extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'crcd';
	const TITLE      = 'Credit Card Code';
	const SUMMARY    = 'View / Edit Credit Card Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::crcdUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Mar\ArCreditCardCode();
	}

	public static function getCodeTable() {
		return Codes\Mar\Crcd::instance();
	}

/* =============================================================
	Render HTML / JS
============================================================= */
	protected static function renderList(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mar/crcd/list.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderModal(WireData $data) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mar/crcd/edit-modal.twig', ['manager' => $codeTable]);
	}
}
