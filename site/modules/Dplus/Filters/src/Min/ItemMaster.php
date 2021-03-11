<?php namespace Dplus\Filters\Min;

use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
use Dplus\Filters\AbstractFilter;

use ItemMasterItemQuery, ItemMasterItem as ItemMasterItemClass;

class ItemMaster extends AbstractFilter {
	const MODEL = 'ItemMasterItem';

/* =============================================================
	Abstract Contract Functions
============================================================= */
	public function initQuery() {
		$this->query = ItemMasterItemQuery::create();
	}

	public function _search($q) {
		$columns = [
			ItemMasterItemClass::aliasproperty('itemid'),
			ItemMasterItemClass::aliasproperty('description'),
			ItemMasterItemClass::aliasproperty('description2'),
		];
		$this->query->search_filter($columns, strtoupper($q));
	}

	/**
	 * Return if Item Exists
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function exists($itemID) {
		return boolval(ItemMasterItemQuery::create()->filterByItemid($itemID)->count());
	}

/* =============================================================
	Misc Query Functions
============================================================= */
	/**
	 * Return Position of ItemMasterItem in results
	 * @param  ItemMasterItemClass $item ItemMasterItem
	 * @return int
	 */
	public function position(ItemMasterItemClass $item) {
		$results = $this->query->find();
		return $results->search($item);
	}

	/**
	 * Return Position of Item in results
	 * @param  ItemMasterItemClass|string $item ItemMasterItem|Item ID
	 * @return int
	 */
	public function positionQuick($item) {
		$itemID = $item;
		if (is_object($item)) {
			$itemID = $item->itemid;
		}
		$q = ItemMasterItemQuery::create();
		$q->execute_query('SET @rownum = 0');
		$table = $q->getTableMap()::TABLE_NAME;
		$sql = "SELECT x.position FROM (SELECT InitItemNbr, @rownum := @rownum + 1 AS position FROM $table) x WHERE InitItemNbr = :itemid";
		$params = [':itemid' => $itemID];
		$stmt = $q->execute_query($sql, $params);
		return $stmt->fetchColumn();
	}
}
