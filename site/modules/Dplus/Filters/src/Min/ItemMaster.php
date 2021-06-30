<?php namespace Dplus\Filters\Min;
// Dplus Model
use ItemMasterItemQuery, ItemMasterItem as Model;
use WarehouseInventoryQuery, WarehouseInventory;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
 * Wrapper Class for adding Filters to the ItemMasterItemQuery class
 */
class ItemMaster extends AbstractFilter {
	const MODEL = 'ItemMasterItem';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$columns = [
			Model::aliasproperty('itemid'),
			Model::aliasproperty('description'),
			Model::aliasproperty('description2'),
		];
		$this->query->search_filter($columns, strtoupper($q));
	}

/* =============================================================
	Base Filter Functions
============================================================= */
	/**
	 * Filter ItemIDs by Item's active in X Warehouse
	 * @param  string $whseID Warehouse ID
	 * @return void
	 */
	 public function active($whseID = '') {
 		if (empty($whseID)) {
 			$whseID = $this->wire('user')->whseid;
 		}
 		$this->query
 		->useWarehouseInventoryQuery()
 			->filterByWarehouseid($whseID)
 			->filterByStatus(WarehouseInventory::STATUS_ACTIVE)
 		->endUse();
 	}

/* =============================================================
	Misc Query Functions
============================================================= */
	/**
	 * Return if Item Exists
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function exists($itemID) {
		return boolval(ItemMasterItemQuery::create()->filterByItemid($itemID)->count());
	}

	/**
	 * Return Position of Item in results
	 * @param  Model|string $item ItemMasterItem|Item ID
	 * @return int
	 */
	public function positionQuick($item) {
		$itemID = $item;
		if (is_object($item)) {
			$itemID = $item->itemid;
		}
		$q = $this->getQueryClass();
		$q->executeQuery('SET @rownum = 0');
		$table = $q->getTableMap()::TABLE_NAME;
		$sql = "SELECT x.position FROM (SELECT InitItemNbr, @rownum := @rownum + 1 AS position FROM $table) x WHERE InitItemNbr = :itemid";
		$stmt = $q->executeQuery($sql, [':itemid' => $itemID]);
		return $stmt->fetchColumn();
	}
}
