<?php namespace Dplus\Filters\Mpo;
// Propel Classes
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Model
use PoConfirmCodeQuery, PoConfirmCode as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
* Wrapper Class for PoConfirmCodeQuery
*/
class PoConfirmCode extends AbstractFilter {
	const MODEL = 'PoConfirmCode';

/* =============================================================
	1. Abstract Contract Functions
============================================================= */
	public function _search($q) {
		$columns = [
			Model::aliasproperty('code'),
			Model::aliasproperty('description'),
		];
		$this->query->searchFilter($columns, strtoupper($q));
	}

/* =============================================================
	2. Base Filter Functions
============================================================= */


/* =============================================================
	3. Filter Input Functions
============================================================= */

/* =============================================================
	4. Misc Query Functions
============================================================= */
	/**
	 * Return if Code Exists
	 * @param  string $code
	 * @return bool
	 */
	public function exists($code) {
		return boolval($this->getQueryClass()->filterById($code)->count());
	}

	/**
	 * Return Position of Item in results
	 * @param  Model|string $item PrWorkCenter|Code
	 * @return int
	 */
	public function positionQuick($code) {
		$code = $code;
		if (is_object($code)) {
			$code = $code->id;
		}
		$q = $this->getQueryClass();
		$q->execute_query('SET @rownum = 0');
		$table = $q->getTableMap()::TABLE_NAME;
		$col   = Model::aliasproperty('code');
		$sql = "SELECT x.position FROM (SELECT $col, @rownum := @rownum + 1 AS position FROM $table) x WHERE $col = :code";
		$params = [':code' => $code];
		$stmt = $q->executeQuery($sql, $params);
		return $stmt->fetchColumn();
	}
}
