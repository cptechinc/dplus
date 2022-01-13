<?php namespace Dplus\Wm\Reports\Inventory\StockStatus;
// ProcessWire
use ProcessWire\WireData;
// Dplus Inventory
use Dplus\Wm\Inventory\Whse\StockStatus as Inventory;
// Dplus Warehouse Management Report
use Dplus\Wm\Reports\Inventory\StockStatus;

/**
 * Stock Status
 * Gathers Data for Provalley's Stock Status Report
 */
class Provalley extends StockStatus {

	const COLUMNS_ITEM = [
		'binid'      => ['label' => 'Bin', 'justify' => 'left'],
		'itemid'     => ['label' => 'Item ID', 'justify' => 'left'],
		'itemDesc'   => ['label' => 'Item Description', 'justify' => 'left'],
	];

	const COLUMNS_LOT = [
		'lotserial'  => ['label' => 'Lot Number', 'justify' => 'left'],
		'lotref'     => ['label' => 'Vendor Lot Number', 'justify' => 'left'],
		'expiredate' => ['label' => 'Prod Date', 'justify' => 'right'],
		'days'       => ['label' => 'Age', 'justify' => 'right'],
		'qty'        => ['label' => 'Weight', 'justify' => 'right'],
	];

	public function __construct() {
		$this->initDataFetcher();
	}

	/**
	 * Return Report Data
	 * @return array
	 */
	private function initDataFetcher() {
		$this->dataFetcher = new Inventory\Provalley();
	}
}
