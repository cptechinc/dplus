<?php namespace Controllers\Mar\Armain;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// ProcessWire
use ProcessWire\WireData;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Codes;


class Csv extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'csv';
	const TITLE      = 'Ship Via Code';
	const SUMMARY    = 'View / Edit Ship Via Code';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::csvUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Mar\Shipvia();
	}

	public static function getCodeTable() {
		return Codes\Mar\Csv::instance();
	}

/* =============================================================
	Render HTML / JS
============================================================= */
	protected static function renderList(WireData $data, PropelModelPager $codes) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mar/csv/list.twig', ['manager' => $codeTable, 'codes' => $codes]);
	}

	protected static function renderModal(WireData $data) {
		$codeTable = static::getCodeTable();
		return self::pw('config')->twig->render('code-tables/mar/csv/edit-modal.twig', ['manager' => $codeTable]);
	}

/* =============================================================
	Supplemental
============================================================= */
	/**
	 * Return CodeTable field Config Data
	 * NOTE: Keep public for classes that are a copy of another, in a different menu
	 * @param  WireData $data
	 * @return array
	 */
	public static function getCodeTableFieldConfigData(WireData $data) {
		$configData = parent::getCodeTableFieldConfigData($data);
		$table = static::getCodeTable();
		$configData['useroute'] = ['enabled' => $table->fieldAttribute('useroute', 'enabled')];
		return $configData;
	}

}
