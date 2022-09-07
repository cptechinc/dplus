<?php namespace Controllers\Mpm\Pmmain;
// ProcessWire 
use ProcessWire\WireData;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus
use Dplus\Filters;
use Dplus\Codes;

/**
 * Rcm
 * 
 * Controller for handling HTTP Requests for the Rcm Codetable
 */
class Rcm extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'rcm';
	const TITLE      = 'Resource / Operator';
	const SUMMARY    = 'View / Edit Resource / Operator Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::rcmUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Mpm\PrResource();
	}

	public static function getCodeTable() {
		return Codes\Mpm\Rcm::instance();
	}

/* =============================================================
	Render HTML / JS
============================================================= */
	protected static function renderList(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mpm/rcm/list.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}
	
	protected static function renderModal(WireData $data) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mpm/rcm/edit-modal.twig', ['manager' => $codeTable]);
	}
}