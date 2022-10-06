<?php namespace Controllers\Mgl\Glmain;
// ProcessWire 
use ProcessWire\WireData;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus
use Dplus\Filters;
use Dplus\Codes;

/**
 * Ttm
 * 
 * Controller for handling HTTP Requests for the Ttm Codetable
 */
class Ttm extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'ttm';
	const TITLE      = 'Statement Text Code';
	const SUMMARY    = 'View / Edit Statement Text Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::ttmUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Mgl\GlTextCode();
	}

	public static function getCodeTable() {
		return Codes\Mgl\Ttm::instance();
	}

/* =============================================================
	Render HTML / JS
============================================================= */
	protected static function renderList(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mgl/ttm/list.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderListForPrinting(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mgl/ttm/list-print.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderModal(WireData $data) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mgl/ttm/edit-modal.twig', ['manager' => $codeTable]);
	}
}