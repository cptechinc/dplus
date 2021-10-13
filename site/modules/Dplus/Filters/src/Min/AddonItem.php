<?php namespace Dplus\Filters\Min;

use PDO;
// Dplus Model
use ItemAddonItemQuery, ItemAddonItem as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
 * Wrapper Class for adding Filters to the ItemAddonItemQuery class
 */
class AddonItem extends AbstractFilter {
	const MODEL = 'ItemAddonItem';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$columns = [
			Model::aliasproperty('itemid'),
			Model::aliasproperty('addonitemid')
		];
		$this->query->searchFilter($columns, strtoupper($q));
	}

/* =============================================================
	Base Filter Functions
============================================================= */

/* =============================================================
	Misc Query Functions
============================================================= */
	/**
	 * Return if ItemAddonItem Exists
	 * @param  string $itemID   Item ID
	 * @param  string $addonID  Addon Item ID ID
	 * @return bool
	 */
	public function exists($itemID, $addonID) {
		$q = ItemAddonItemQuery::create();
		$q->filterByParentitemid($itemID);
		$q->filterByChilditemid($addonID);
		return boolval($q->count());
	}

	/**
	 * Return Position of Item in results
	 * @param  Model|string $itemID   Parent Item ID
	 * @param  string       $addonID  Addon Item ID ID
	 * @return int
	 */
	public function positionQuick($itemID, $addonID = '') {
		$xref = $itemID;
		if (is_object($xref)) {
			$itemID = $xref->parentitemid;
			$addonID = $xref->childitemid;
		}
		$this->getQueryClass()->executeQuery('SET @rownum = 0');
		$table = $this->getPositionSubSql();

		$sql = "SELECT x.position FROM ($table) x WHERE InitItemNbr = :itemid AND AdonAddItemNbr = :addonid";
		$stmt = $this->getPreparedStatementWrapper($sql);
		$stmt->bindValue(':itemid', $itemID, PDO::PARAM_STR);
		$stmt->bindValue(':addonid', $addonID, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	/**
	 * Return Sub Query for getting result set with custid and position
	 * @return string
	 */
	private function getPositionSubSql() {
		$table = $this->query->getTableMap()::TABLE_NAME;
		$sql = "SELECT InitItemNbr, AdonAddItemNbr, @rownum := @rownum + 1 AS position FROM $table";
		$whereClause = $this->getWhereClauseString();
		if (empty($whereClause) === false) {
			$sql .= ' WHERE ' . $whereClause;
		}
		return $sql;
	}
}
