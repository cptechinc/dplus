<?php namespace Dplus\Filters\Msa;
// PHP Core
use PDO;
// Dplus Model
use MsaSysopCodeQuery, MsaSysopCode as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Recordlocker
use Dplus\RecordLocker\Locker as Recordlocker;
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
		if (in_array($system, self::SYSTEMS)) {
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
	public function positionQuick($code) {
		$id = $code;
		if (is_object($code)) {
			$id     = $code->id;
			$system = $code->system;
		}
		if (is_string($code)) {
			$keys   = explode(Recordlocker::GLUE, $code);
			$system = $keys[0];
			$id     = $keys[1];
		}
		$this->getQueryClass()->executeQuery('SET @rownum = 0');
		$table = $this->getPositionSubSql();
		$sql  = "SELECT x.position FROM ($table) x WHERE OptnSystem = :system AND OptnCode = :code";
		$stmt = $this->getPreparedStatementWrapper($sql);
		$stmt->bindValue(':system', $system, PDO::PARAM_STR);
		$stmt->bindValue(':code', $id, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	/**
	 * Return Sub Query for getting result set with custid and position
	 * @return string
	 */
	private function getPositionSubSql() {
		$table = $this->query->getTableMap()::TABLE_NAME;
		$sql = "SELECT OptnSystem, OptnCode, @rownum := @rownum + 1 AS position FROM $table";
		$whereClause = $this->getWhereClauseString();
		$orderByClause = $this->getOrderByClause();

		if (empty($whereClause) === false) {
			$sql .= ' WHERE ' . $whereClause;
		}

		if (empty($orderByClause) === false) {
			$sql .= ' ORDER BY ' . $orderByClause;
		}
		return $sql;
	}
}
