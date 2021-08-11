<?php namespace Dplus\Filters\Min;
use PDO;
// Dplus Model
use InvItem2ItemQuery, InvItem2Item as Model;
use WarehouseInventoryQuery, WarehouseInventory; // WAREHOUSE ITEM MASTER
use WhseLotserialQuery, WhseLotserial;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
 * Wrapper Class for adding Filters to the InvItem2ItemQuery class
 */
class I2i extends AbstractFilter {
	const MODEL = 'InvItem2Item';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$columns = [
			Model::aliasproperty('parentitemid'),
			Model::aliasproperty('childitemid'),
			Model::aliasproperty('whseid')
		];
		$this->query->search_filter($columns, strtoupper($q));
	}

/* =============================================================
	Base Filter Functions
============================================================= */

/* =============================================================
	Misc Query Functions
============================================================= */
	/**
	 * Return if Item2Item Exists
	 * @param  string $parentID Parent Item ID
	 * @param  string $childID  Child Item ID
	 * @return bool
	 */
	public function exists($parentID, $childID) {
		$q = InvItem2ItemQuery::create();
		$q->filterByParentitemid($parentID);
		$q->filterByChilditemid($childID);
		return boolval($q->count());
	}

	/**
	 * Return Position of Item in results
	 * @param  Model|string $item InvItem2Item|Item ID
	 * @return int
	 */
	public function positionQuick($item) {
		$itemID = $item;
		if (is_object($item)) {
			$itemID = $item->itemid;
		}
		$q = $this->getQueryClass()->executeQuery('SET @rownum = 0');
		$table = $this->getPositionSubSql();

		$sql = "SELECT x.position FROM ($table) x WHERE InitItemNbr = :itemid";
		$stmt = $this->getPreparedStatementWrapper($sql);
		$stmt->bindValue(':itemid', $itemID, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	/**
	 * Return Sub Query for getting result set with custid and position
	 * @return string
	 */
	private function getPositionSubSql() {
		$table = $this->query->getTableMap()::TABLE_NAME;
		$sql = "SELECT InitItemNbr, @rownum := @rownum + 1 AS position FROM $table";
		$whereClause = $this->getWhereClauseString();
		if (empty($whereClause) === false) {
			$sql .= ' WHERE ' . $whereClause;
		}
		return $sql;
	}
}
