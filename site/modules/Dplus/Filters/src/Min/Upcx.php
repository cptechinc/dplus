<?php namespace Dplus\Filters\Min;
// Dplus Model
use ItemXrefUpcQuery, ItemXrefUpc as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
 * Wrapper Class for ItemXrefUpcQuery
 */
class Upcx extends AbstractFilter {
	const MODEL = 'ItemXrefUpc';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$columns = [
			Model::aliasproperty('itemid'),
			Model::aliasproperty('upc'),
		];
		$this->query->search_filter($columns, strtoupper($q));
	}

	/**
	 * Filter Query with Input Data
	 * @param  WireInput $input Input Data
	 * @return self
	 */
	public function _filterInput(WireInput $input) {
		$this->itemidInput($input);
	}

/* =============================================================
	2. Base Filter Functions
============================================================= */
	/**
	 * Filter the Query on the Customer ID column
	 * @param  string|array $itemID      Item ID
	 * @param  string       $comparison
	 * @return self
	 */
	public function itemid($itemID, $comparison = null) {
		if ($itemID)  {
			$this->query->filterByItemid($itemID, $comparison);
		}
		return $this;
	}

/* =============================================================
	3. Input Functions
============================================================= */
	/**
	 * Filter Query by ItemID using Input Data
	 * @param  WireInput $input Input Data
	 * @return self
	 */
	public function itemidInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$itemID = $values->ouritemID ? $values->array('ouritemID') : $values->array('itemID');
		return $this->itemid($itemID);
	}

/* =============================================================
	4. Misc Query Functions
============================================================= */
	/**
	 * Return if Item Exists
	 * @param  string $upc Item ID
	 * @return bool
	 */
	public function exists($upc) {
		return boolval($this->getQueryClass()->filterByUpc($upc)->count());
	}

	/**
	 * Return Position of Item in results
	 * @param  Model|string $xref ItemXrefUpc|UPC Code
	 * @return int
	 */
	public function positionQuick($xref) {
		$upc = $xref;
		if (is_object($xref)) {
			$upc = $xref->upc;
		}
		$q = $this->getQueryClass();
		$q->execute_query('SET @rownum = 0');
		$table = $q->getTableMap()::TABLE_NAME;
		$sql = "SELECT x.position FROM (SELECT UpcxCode, @rownum := @rownum + 1 AS position FROM $table) x WHERE UpcxCode = :upc";
		$params = [':upc' => $upc];
		$stmt = $q->execute_query($sql, $params);
		return $stmt->fetchColumn();
	}
}
