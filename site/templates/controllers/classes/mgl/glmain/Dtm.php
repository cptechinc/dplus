<?php namespace Controllers\Mgl\Glmain;
// ProcessWire 
use ProcessWire\WireData;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus
use Dplus\Filters;
use Dplus\Codes;

/**
 * Dtm
 * 
 * Controller for handling HTTP Requests for the Dtm Codetable
 */
class Dtm extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'dtm';
	const TITLE      = 'Distribution Code';
	const SUMMARY    = 'View / Edit Distribution Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::dtmUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Mgl\GlDistCode();
	}

	public static function getCodeTable() {
		return Codes\Mgl\Dtm::instance();
	}

/* =============================================================
	Render HTML / JS
============================================================= */
	protected static function renderList(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mgl/dtm/list.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderListForPrinting(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mgl/dtm/list-print.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderModal(WireData $data) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mgl/dtm/edit-modal.twig', ['manager' => $codeTable]);
	}
}