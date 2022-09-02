<?php namespace Dplus\Filters\Msa;
// PHP Core
use PDO;
// Dplus Model
use SysopOptionalCodeQuery, SysopOptionalCode as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
* Wrapper Class for SysopOptionalCodeQuery
* For Searching Optional Codes
*/
class SysopOptionalCode extends AbstractFilter {
	const MODEL   = 'SysopOptionalCode';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q, $cols = []) {
		$cols = array_filter($cols);
		$columns = [];

		if (empty($cols)) {
			$columns = [
				Model::aliasproperty('code'),
				Model::aliasproperty('description'),
			];
			$this->query->searchFilter($columns, strtoupper($q));
			return true;
		}

		foreach ($cols as $col) {
			if (Model::aliasproperty_exists($col)) {
				$columns[] = Model::aliasproperty($col);
			}
		}
		if (empty($columns)) {
			return true;
		}
		$this->query->searchFilter($columns, strtoupper($q));
		return true;
	}

/* =============================================================
	2. Base Filter Functions
============================================================= */
	/**
	 * Filter System
	 * @return self
	 */
	public function system($system) {
		if (in_array($system, MsaSysopCode::SYSTEMS)) {
			$this->query->filterBySystem($system);
		}
		return $this;
	}

/* =============================================================
	3. Input Filter Functions
============================================================= */

/* =============================================================
	4. Misc Query Functions
============================================================= */
	/**
	 * Return if Note ID Exists
	 * @param  string $id Note ID
	 * @return bool
	 */
	public function exists($id) {
		return boolval($this->getQueryClass()->filterById($id)->count());
	}

	/**
	 * Return Position of Item in results
	 * @param  Model|string $note SysopOptionalCode|Note ID
	 * @return int
	 */
	public function positionQuick($note) {
		$id = $note;
		if (is_object($note)) {
			$id = $note->id;
		}
		$q = $this->getQueryClass()->executeQuery('SET @rownum = 0');
		$table = $this->getPositionSubSql();
		$col = Model::aliasproperty('id');

		$sql = "SELECT x.position FROM ($table) x WHERE $col = :id";
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
		$col = Model::aliasproperty('id');
		$sql = "SELECT $col, @rownum := @rownum + 1 AS position FROM $table";
		$whereClause = $this->getWhereClauseString();

		if (empty($whereClause) === false) {
			$sql .= ' WHERE ' . $whereClause;
		}
		return $sql;
	}
}
