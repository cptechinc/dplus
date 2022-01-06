<?php namespace Dplus\Filters\Min;
// Propel Classes
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Model
use WarehouseQuery, Warehouse as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
 * Wrapper Class for WarehouseQuery
 */
class Warehouse extends AbstractFilter {
	const MODEL = 'Warehouse';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$columns = array(
			Model::aliasproperty('id'),
			Model::aliasproperty('name'),
		);
		$this->query->searchFilter($columns, strtoupper($q));
	}

	/**
	 * Filter Query with Input Data
	 * @param  WireInput $input Input Data
	 * @return self
	 */
	public function _filterInput(WireInput $input) {
		$this->whseidInput($input);
	}

/* =============================================================
	Base Filter Functions
============================================================= */
	/**
	 * Filter the Query on the Warehouse ID column
	 * @param  string|array $whseID      Warehouse ID
	 * @param  string       $comparison
	 * @return self
	 */
	public function whseid($whseID, $comparison = null) {
		if ($whseID)  {
			$this->query->filterByWhseid($whseID, $comparison);
		}
		return $this;
	}

/* =============================================================
	Input Functions
============================================================= */
	/**
	 * Filter Query by WhseID using Input Data
	 * @param  WireInput $input Input Data
	 * @return self
	 */
	public function whseidInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		return $this->whseid($values->array('whseID'));
	}

/* =============================================================
	Misc Query Functions
============================================================= */

}
