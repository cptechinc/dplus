<?php namespace Dplus\CodeValidators\Itm;

use ProcessWire\WireData;

use Propel\Runtime\ActiveQuery\Criteria;

use WarehouseInventoryQuery, WarehouseInventory;

use Dplus\CodeValidators\Min\Itm;


/**
 * Itm
 *
 * Class for Validating ITM Warehouse codes
 */
class Warehouse extends Itm {
/* =============================================================
	Itm Warehouse Functions
============================================================= */
	/**
	 * Validate Code ABC value
	 * @param  string $code Code ABC
	 * @return bool
	 */
	public function codeabc($code) {
		return preg_match(WarehouseInventory::REGEX_CODEABC, $code);
	}

	/**
	 * Validate Warehouse Inventory Status
	 * @param  string $status Status Code
	 * @return bool
	 */
	public function status($status) {
		return array_key_exists($status, WarehouseInventory::STATUS_DESCRIPTIONS);
	}

	/**
	 * Validate Warehouse Inventory Special Order
	 * @param  string $code Special Order Code
	 * @return bool
	 */
	public function specialorder($code) {
		return array_key_exists($code, WarehouseInventory::SPECIALORDER_DESCRIPTIONS);
	}
}
