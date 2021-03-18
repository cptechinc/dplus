<?php namespace Dplus\Filters\Mki;

use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
use Dplus\Filters\AbstractFilter;

use InvKitQuery, InvKit as Model;

class Kim extends AbstractFilter {
	const MODEL = 'InvKit';

/* =============================================================
	Abstract Contract Functions
============================================================= */
	public function initQuery() {
		$this->query = InvKitQuery::create();
	}

	public function _search($q) {
		$columns = [
			Model::aliasproperty('itemid'),
		];
		$this->query->search_filter($columns, strtoupper($q));
	}

	/**
	 * Return if Item Exists
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function exists($itemID) {
		return boolval(InvKitQuery::create()->filterByItemid($itemID)->count());
	}

/* =============================================================
	Misc Query Functions
============================================================= */
	/**
	 * Return Position of InvKit in results
	 * @param  Model $item InvKit
	 * @return int
	 */
	public function position(Model $item) {
		$results = $this->query->find();
		return $results->search($item);
	}

	/**
	 * Return Position of Item in results
	 * @param  Model|string $item InvKit|Item ID
	 * @return int
	 */
	public function positionQuick($item) {
		$itemID = $item;
		if (is_object($item)) {
			$itemID = $item->itemid;
		}
		$q = InvKitQuery::create();
		$q->execute_query('SET @rownum = 0');
		$table = $q->getTableMap()::TABLE_NAME;
		$sql = "SELECT x.position FROM (SELECT InitItemNbr, @rownum := @rownum + 1 AS position FROM $table) x WHERE InitItemNbr = :itemid";
		$params = [':itemid' => $itemID];
		$stmt = $q->execute_query($sql, $params);
		return $stmt->fetchColumn();
	}
}
