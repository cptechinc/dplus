<?php namespace Controllers\Min\Inmain;
// ProcessWire 
use ProcessWire\WireData;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus
use Dplus\Filters;
use Dplus\Codes;

/**
 * Msdsm
 * 
 * Controller for handling HTTP Requests for the Iplm Codetable
 */
class Msdsm extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'msdsm';
	const TITLE      = 'Material Safety Data Sheet Code';
	const SUMMARY    = 'View / Edit Material Safety Data Sheet Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::msdsmUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Min\MsdsCode();
	}

	public static function getCodeTable() {
		return Codes\Min\Msdsm::instance();
	}

/* =============================================================
	Render HTML / JS
============================================================= */
	protected static function renderList(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/min/msdsm/list.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderModal(WireData $data) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/min/msdsm/edit-modal.twig', ['manager' => $codeTable]);
	}
}