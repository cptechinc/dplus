<?php namespace Controllers\Mso\Somain;
// ProcessWire 
use ProcessWire\WireData;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus
use Dplus\Filters;
use Dplus\Codes;


class Lsm extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'lsm';
	const TITLE      = 'Lost Sales Reason';
	const SUMMARY    = 'View / Edit Lost Sales Reason Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::lsmUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Mso\LostSalesCode();
	}

	public static function getCodeTable() {
		return Codes\Mso\Lsm::instance();
	}

/* =============================================================
	Render HTML / JS
============================================================= */
	protected static function renderList(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mso/lsm/list.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderModal(WireData $data) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mso/lsm/edit-modal.twig', ['manager' => $codeTable]);
	}
}