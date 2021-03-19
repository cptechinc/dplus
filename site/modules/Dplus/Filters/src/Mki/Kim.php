<?php namespace Dplus\Filters\Mki;
// Dplus Model
use InvKitQuery, InvKit as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
* Wrapper Class for InvKitQuery
*/
class Kim extends AbstractFilter {
	const MODEL = 'InvKit';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$columns = [
			Model::aliasproperty('itemid'),
		];
		$this->query->search_filter($columns, strtoupper($q));
	}

/* =============================================================
	2. Base Filter Functions
============================================================= */

/* =============================================================
	3. Input Filter Functions
============================================================= */

/* =============================================================
	4. Misc Query Functions
============================================================= */
	/**
	 * Return if Item Exists
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function exists($itemID) {
		return boolval(InvKitQuery::create()->filterByItemid($itemID)->count());
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
		$q = $this->getQueryClass();
		$q->execute_query('SET @rownum = 0');
		$table = $q->getTableMap()::TABLE_NAME;
		$sql = "SELECT x.position FROM (SELECT InitItemNbr, @rownum := @rownum + 1 AS position FROM $table) x WHERE InitItemNbr = :itemid";
		$params = [':itemid' => $itemID];
		$stmt = $q->execute_query($sql, $params);
		return $stmt->fetchColumn();
	}
}
