<?php namespace Dplus\Filters\Mar;
// Dplus Model
use SalesPersonQuery, SalesPerson as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
 * Wrapper Class for adding Filters to the SalesPersonQuery class
 */
class SalesPerson extends AbstractFilter {
	const MODEL = 'SalesPerson';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q, $cols = []) {
		$columns = [
			Model::get_aliasproperty('contactid'),
			Model::get_aliasproperty('title'),
		];
		$this->query->search_filter($columns, strtoupper($q));
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
