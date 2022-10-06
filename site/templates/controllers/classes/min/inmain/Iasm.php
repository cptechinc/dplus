<?php namespace Controllers\Min\Inmain;
// Dplus
use Dplus\Filters;
use Dplus\Codes;

/**
 * Iasm
 * 
 * Controller for handling CRUD Requests for the Iasm Codetable
 */
class Iasm extends AbstractCodeTableController {
	const DPLUSPERMISSION = 'iasm';
	const TITLE      = 'Inventory Assortment';
	const SUMMARY    = 'View / Edit Inventory Assortment Codes';
	const SHOWONPAGE = 10;

	public static function _url() {
		return Menu::iasmUrl();
	}

	public static function getCodeFilter() {
		return new Filters\Min\InvAssortmentCode();
	}

	public static function getCodeTable() {
		return Codes\Min\Iasm::instance();
	}
}