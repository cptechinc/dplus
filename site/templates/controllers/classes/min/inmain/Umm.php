<?php namespace Controllers\Min\Inmain;
// ProcessWire 
use ProcessWire\WireData;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus
use Dplus\Filters;
use Dplus\Codes;

/**
 * Umm
 * 
 * Controller for handling HTTP Requests for the Umm Codetable
 */
class Umm extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'umm';
	const TITLE      = 'Unit of Measure';
	const SUMMARY    = 'View / Edit Unit of Measure Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::ummUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Min\UnitofMeasure();
	}

	public static function getCodeTable() {
		return Codes\Min\Umm::instance();
	}

/* =============================================================
	Render HTML / JS
============================================================= */
	protected static function renderList(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/min/umm/list.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderListForPrinting(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/min/umm/list-print.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderModal(WireData $data) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/min/umm/edit-modal.twig', ['manager' => $codeTable]);
	}
}