<?php namespace Controllers\Min\Inmain;
// ProcessWire 
use ProcessWire\WireData;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus
use Dplus\Filters;
use Dplus\Codes;

/**
 * Tarm
 * 
 * Controller for handling HTTP Requests for the Tarm Codetable
 */
class Tarm extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'tarm';
	const TITLE      = 'Tariff Code';
	const SUMMARY    = 'View / Edit Tariff Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::tarmUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Min\TariffCode();
	}

	public static function getCodeTable() {
		return Codes\Min\Tarm::instance();
	}

/* =============================================================
	Render HTML / JS
============================================================= */
	protected static function renderList(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/min/tarm/list.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderModal(WireData $data) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/min/tarm/edit-modal.twig', ['manager' => $codeTable]);
	}
}