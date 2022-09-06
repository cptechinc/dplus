<?php namespace Controllers\Mso\Somain;
// ProcessWire 
use ProcessWire\WireData;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus
use Dplus\Filters;
use Dplus\Codes;


class Rgasc extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'rgasc';
	const TITLE      = 'RGA / Return Ship-via Code';
	const SUMMARY    = 'View / Edit RGA / Return Ship-via Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::rgascUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Mso\SoRgaCode();
	}

	public static function getCodeTable() {
		return Codes\Mso\Rgasc::instance();
	}

/* =============================================================
	Render HTML / JS
============================================================= */
	protected static function renderList(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mso/rgasc/list.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderModal(WireData $data) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mso/rgasc/edit-modal.twig', ['manager' => $codeTable]);
	}
}