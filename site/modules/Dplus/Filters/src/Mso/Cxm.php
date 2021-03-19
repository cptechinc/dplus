<?php namespace Dplus\Filters\Mso;
// Propel Classes
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Model
use ItemXrefCustomerQuery, ItemXrefCustomer as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page, ProcessWire\User;
// Dplus filters
use Dplus\Filters\AbstractFilter;

/**
 * Wrapper Class for adding Filters to the ItemXrefCustomerQuery class
 */
class Cxm extends AbstractFilter {
	const MODEL = 'ItemXrefCustomer';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$columns = [
			Model::get_aliasproperty('itemid'),
			Model::get_aliasproperty('custitemid'),
			Model::get_aliasproperty('description')
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
		$this->custidInput($input);
	}
/* =============================================================
	2. Base Filter Functions
============================================================= */
	/**
	 * Filter the Query on the Customer ID column
	 * @param  string|array $custID      Customer ID
	 * @param  string       $comparison
	 * @return self
	 */
	public function custid($custID, $comparison = null) {
		if ($custID)  {
			$this->query->filterByCustid($custID, $comparison);
		}
		return $this;
	}

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
	 * Filter the Query on the Customer ID column
	 * @param  WireInput $input
	 * @return self
	 */
	public function custidInput($input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		if ($values->custID) {
			$this->custid($values->array('custID'));
		}
		return $this;
	}

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
}
