<?php namespace Dplus\Wm\Reports\Inventory;
// ProcessWire
use ProcessWire\WireData;
// Dplus Inventory
use Dplus\Wm\Inventory\Whse\StockStatus as Inventory;

/**
 * Stock Status
 * Gathers Data for Stock Status Report
 */
class StockStatus extends WireData {
	protected $dataFetcher;
	protected $reportData;

	const COLUMNS_ITEM = [
		'binid'      => ['label' => 'Bin', 'justify' => 'left'],
		'itemid'     => ['label' => 'Item ID', 'justify' => 'left'],
		'itemDesc'   => ['label' => 'Item Description', 'justify' => 'left'],
	];

	const COLUMNS_LOT = [
		'lotserial'  => ['label' => 'Lot Number', 'justify' => 'left'],
		'lotref'     => ['label' => 'Vendor Lot Number', 'justify' => 'left'],
		'qty'        => ['label' => 'Weight', 'justify' => 'right'],
	];

	public function __construct() {
		$this->initDataFetcher();
	}

	/**
	 * Return all Column definitions
	 * @return array
	 */
	public function getAllColumns() {
		return ['item' => static::COLUMNS_ITEM, 'lot' => static::COLUMNS_LOT];
	}

	/**
	 * Initialize Inventory Fetcher
	 * @return void
	 */
	private function initDataFetcher() {
		$this->dataFetcher = new Inventory();
	}

	/**
	 * Gets Report Data and sets $this->reportData
	 * @return void
	 */
	public function generate() {
		$this->reportData = $this->dataFetcher->getData();
	}

	/**
	 * Return Report Data
	 * @return array
	 */
	public function getReportData() {
		return $this->reportData;
	}
}
