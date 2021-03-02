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
	public function position(ItemMasterItemClass $p) {
		$people = $this->query->find();
		return $people->search($p);
	}
}
