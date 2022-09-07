<?php namespace Controllers\Map\Apmain;
// ProcessWire 
use ProcessWire\WireData;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus
use Dplus\Filters;
use Dplus\Codes;

/**
 * Bum
 * 
 * Controller for handling HTTP Requests for the Bum Codetable
 */
class Bum extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'bum';
	const TITLE      = 'Vendor Buyer Code';
	const SUMMARY    = 'View / Edit Vendor Buyer Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::bumUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Map\ApBuyer();
	}

	public static function getCodeTable() {
		return Codes\Map\Bum::instance();
	}

/* =============================================================
	Render HTML / JS
============================================================= */
	protected static function renderList(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/map/bum/list.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderModal(WireData $data) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/map/bum/edit-modal.twig', ['manager' => $codeTable]);
	}
}