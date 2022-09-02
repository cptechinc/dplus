<?php namespace Dplus\Filters;
// ProcessWire Classes
// use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
* Abstract Class for Query Wrappers for Code Tables
*/
abstract class CodeFilter extends AbstractFilter {

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	
	public function _search($q, $cols = []) {
		$model = $this->modelName();
		$columns = [];
		$cols = array_filter($cols);

		if (empty($cols)) {
			$columns = [$model::aliasproperty('description')];
	
			if ($model::aliasproperty_exists('id')) {
				$columns[] = $model::aliasproperty('id');
			}
	
			if ($model::aliasproperty_exists('code') && $model::aliasproperty_exists('id') === false) {
				$columns[] = $model::aliasproperty('code');
			}
			$this->query->searchFilter($columns, strtoupper($q));
			return true;
		}

		foreach ($cols as $col) {
			if ($model::aliasproperty_exists($col)) {
				$columns[] = $model::aliasproperty($col);
			}
			$this->query->searchFilter($columns, strtoupper($q));
			return true;
		}
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
	 * @param  Model|string $item SysLoginGroup|Code
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
		$model = $this->modelName();
		$col   = '';

		if ($model::aliasproperty_exists('code')) {
			$col = $model::aliasproperty('code');
		}

		if ($model::aliasproperty_exists('id')) {
			$col = $model::aliasproperty('id');
		}
		$sql = "SELECT x.position FROM (SELECT $col, @rownum := @rownum + 1 AS position FROM $table) x WHERE $col = :code";
		$params = [':code' => $code];
		$stmt = $q->executeQuery($sql, $params);
		return $stmt->fetchColumn();
	}

	public function searchWildcardId($q) {
		$model = $this->modelName();
		$col = '';

		if ($model::aliasproperty_exists('code')) {
			$col = $model::aliasproperty('code');
		}

		if ($model::aliasproperty_exists('id')) {
			$col = $model::aliasproperty('id');
		}

		if ($col == '') {
			return false;
		}
		$qWildcard = $this->wildcardify($q, ['prepend' => true]);

	}
}
