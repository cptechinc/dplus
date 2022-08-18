<?php namespace Dplus\Filters\Min;
// Propel Classes
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Model
use WarehouseBinQuery, WarehouseBin as Model;
use WarehouseQuery, Warehouse;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
 * Wrapper Class for WarehouseBinQuery
 */
class WarehouseBin extends AbstractFilter {
	const MODEL = 'WarehouseBin';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q, $cols = []) {

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
	/**
	 * Return Arrangment Code for Warehouse
	 * @param  string $whseID [description]
	 * @return string         L list | R ranged
	 */
	public function getWarehouseBinArrangement($whseID) {
		return WarehouseQuery::create()->select(Warehouse::aliasproperty('arranged'))->findOneByWhseid($whseID);
	}

	/**
	 * Return if Bin Exists for Warehouse ID
	 * @param  string $whseID  Warehouse ID
	 * @param  string $binID   Bin ID
	 * @return bool
	 */
	public function exists($whseID, $binID) {
		$q = $this->query();
		$q->filterByWarehouse($whseID);

		if ($this->getWarehouseBinArrangement($whseID) == Warehouse::BINS_RANGED) {
			$q->condition('from', 'WarehouseBin.BnctBinFrom <= ?', $binID);
			$q->condition('thru', 'WarehouseBin.BnctBinThru >= ?', $binID);
			$q->where(array('from', 'thru'), Criteria::LOGICAL_AND);
		} else {
			$q->filterbyBnctBinFrom($binID);
		}
		return boolval($q->count());
	}
}
