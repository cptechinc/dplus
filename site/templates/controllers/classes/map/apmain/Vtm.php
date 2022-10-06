<?php namespace Controllers\Map\Apmain;
// ProcessWire 
use ProcessWire\WireData;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus
use Dplus\Filters;
use Dplus\Codes;

/**
 * Vtm
 * 
 * Controller for handling HTTP Requests for the Vtm Codetable
 */
class Vtm extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'vtm';
	const TITLE      = 'Vendor Type Code';
	const SUMMARY    = 'View / Edit Vendor Type Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::vtmUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Map\ApTypeCode();
	}

	public static function getCodeTable() {
		return Codes\Map\Vtm::instance();
	}

/* =============================================================
	Render HTML / JS
============================================================= */
	protected static function renderList(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/map/vtm/list.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderListForPrinting(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/map/vtm/list-print.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderModal(WireData $data) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/map/vtm/edit-modal.twig', ['manager' => $codeTable]);
	}
}