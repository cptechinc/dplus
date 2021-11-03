<?php namespace Dplus\Filters\Msa;
// PHP Core
use PDO;
// Dplus Model
use MsaSysopCodeQuery, MsaSysopCode as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
* Wrapper Class for MsaSysopCodeQuery
* For Searching Optional Codes
*/
class MsaSysopCode extends AbstractFilter {
	const MODEL   = 'MsaSysopCode';
	const SYSTEMS = [
		'AP', 'AR',
		'IN',
		'MS',
		'SO'
	];

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$model = $this->modelName();
		$columns = [
			$model::aliasproperty('code'),
			$model::aliasproperty('description'),
		];
		$this->query->searchFilter($columns, strtoupper($q));
	}

/* =============================================================
	2. Base Filter Functions
============================================================= */
	/**
	 * Filter System
	 * @return self
	 */
	public function system($system) {
		if (array_key_exists($system, self::SYSTEMS)) {
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
	 * @param  Model|string $note MsaSysopCode|Note ID
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
