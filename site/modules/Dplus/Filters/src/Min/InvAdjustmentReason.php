<?php namespace Dplus\Filters\Min;
use PDO;
// Dplus Model
use InvAdjustmentReasonQuery, InvAdjustmentReason as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
 * Wrapper Class for adding Filters to the InvAdjustmentReasonQuery class
 */
class InvAdjustmentReason extends AbstractFilter {
	const MODEL = 'InvAdjustmentReason';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q, $cols = []) {
		$columns = [
			Model::aliasproperty('id'),
			Model::aliasproperty('description'),
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
	 * Return if Adjustment Reason Code
	 * @param  string $id Code ID
	 * @return bool
	 */
	public function exists($id) {
		return boolval(InvAdjustmentReasonQuery::create()->filterById($id)->count());
	}

	/**
	 * Return Position of Item in results
	 * @param  Model|string $item InvAdjustmentReason|Item ID
	 * @return int
	 */
	public function positionQuick($code) {
		$id = $code;
		if (is_object($item)) {
			$id = $item->id;
		}
		$q = $this->getQueryClass()->executeQuery('SET @rownum = 0');
		$table = $this->getPositionSubSql();

		$sql = "SELECT x.position FROM ($table) x WHERE IntbIarnCode = :id";
		$stmt = $this->getPreparedStatementWrapper($sql);
		$stmt->bindValue(':id', $id, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	/**
	 * Return Sub Query for getting result set with custid and position
	 * @return string
	 */
	private function getPositionSubSql() {
		$table = $this->query->getTableMap()::TABLE_NAME;
		$sql = "SELECT IntbIarnCode, @rownum := @rownum + 1 AS position FROM $table";
		$whereClause = $this->getWhereClauseString();
		if (empty($whereClause) === false) {
			$sql .= ' WHERE ' . $whereClause;
		}
		return $sql;
	}
}
