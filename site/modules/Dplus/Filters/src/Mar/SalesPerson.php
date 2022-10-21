<?php namespace Dplus\Filters\Mar;
// Dplus Model
use SalesPersonQuery, SalesPerson as Model;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
 * Wrapper Class for adding Filters to the SalesPersonQuery class
 */
class SalesPerson extends CodeFilter {
	const MODEL = 'SalesPerson';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q, $cols = []) {
		$columns = [
			Model::get_aliasproperty('code'),
			Model::get_aliasproperty('name'),
		];
		$this->query->searchFilter($columns, strtoupper($q));
	}

/* =============================================================
	4. Misc Query Functions
============================================================= */
	/**
	 * Return Position of Item in results
	 * @param  Model|string $rep SalesPerson|Sales Rep ID
	 * @return int
	 */
	public function positionQuick($rep) {
		$repID = $rep;
		if (is_object($rep)) {
			$repID = $rep->id;
		}
		$q = $this->getQueryClass();
		$q->execute_query('SET @rownum = 0');
		$table = $q->getTableMap()::TABLE_NAME;
		$sql = "SELECT x.position FROM (SELECT ArspSalePer1, @rownum := @rownum + 1 AS position FROM $table) x WHERE ArspSalePer1 = :repid";
		$stmt = $q->execute_query($sql, [':repid' => $repID]);
		return $stmt->fetchColumn();
	}
}
