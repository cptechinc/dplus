<?php namespace Controllers\Mso\Somain;
// ProcessWire 
use ProcessWire\WireData;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus
use Dplus\Filters;
use Dplus\Codes;


class Mfcm extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'mfcm';
	const TITLE      = 'Motor Freight Code';
	const SUMMARY    = 'View / Edit Motor Freight Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::mfcmUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Mso\MotorFreightCode();
	}

	public static function getCodeTable() {
		return Codes\Mso\Mfcm::instance();
	}

/* =============================================================
	Render HTML / JS
============================================================= */
	protected static function renderList(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mso/mfcm/list.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderListForPrinting(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mso/mfcm/list-print.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderModal(WireData $data) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mso/mfcm/edit-modal.twig', ['manager' => $codeTable]);
	}
}