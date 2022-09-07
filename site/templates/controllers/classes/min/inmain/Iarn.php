<?php namespace Controllers\Min\Inmain;
// ProcessWire 
use ProcessWire\WireData;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus
use Dplus\Filters;
use Dplus\Codes;

class Iarn extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'iarn';
	const TITLE      = 'Inventory Adjustment Reason';
	const SUMMARY    = 'View / Edit Inventory Adjustment Reason Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::iarnUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Min\InvAdjustmentReason();
	}

	public static function getCodeTable() {
		return Codes\Min\Iarn::instance();
	}

/* =============================================================
	Render HTML / JS
============================================================= */
	protected static function renderList(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/min/iarn/list.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderModal(WireData $data) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/min/iarn/edit-modal.twig', ['manager' => $codeTable]);
	}
}