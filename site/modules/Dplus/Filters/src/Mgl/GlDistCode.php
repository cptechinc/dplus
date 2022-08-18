<?php namespace Dplus\Filters\Mgl;
// Dplus Model
use GlDistCodeQuery, GlDistCode as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
* Wrapper Class for GlDistCodeQuery
*/
class GlDistCode extends AbstractFilter {
	const MODEL = 'GlDistCode';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q, $cols = []) {
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
	3. Input Filter Functions
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
	 * @param  Model|string $item GlDistCode|Code
	 * @return int
	 */
	public function positionQuick($textcode) {
		$code = $textcode;
		if (is_object($textcode)) {
			$code = $textcode->id;
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
