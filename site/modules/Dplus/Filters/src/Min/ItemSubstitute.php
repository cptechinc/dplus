<?php namespace Dplus\Filters\Min;
// Dplus Model
use ItemSubstituteQuery, ItemSubstitute as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
 * Wrapper Class for adding Filters to the ItemSubstituteQuery class
 */
class ItemSubstitute extends AbstractFilter {
	const MODEL = 'ItemSubstitute';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q, $cols = []) {
		$columns = [
			Model::aliasproperty('itemid'),
		];
		$this->query->search_filter($columns, strtoupper($q));
	}

/* =============================================================
	Base Filter Functions
============================================================= */
	/**
	 * Filter the Query By the Item ID
	 * @param  string $itemID Item ID
	 * @return self
	 */
	public function itemid($itemID) {
		$this->query->filterByItemid($itemID);
		return $this;
	}

/* =============================================================
	Misc Query Functions
============================================================= */

	/**
	 * Return Position of Item in results
	 * @param  Model|string $item ItemSubstitute|Item ID
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
