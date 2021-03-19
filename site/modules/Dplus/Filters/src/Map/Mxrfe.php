<?php namespace Dplus\Filters\Map;
// Propel Classes
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Model
use ItemXrefManufacturerQuery, ItemXrefManufacturer as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page, ProcessWire\User;
// Dplus filters
use Dplus\Filters\AbstractFilter;

/**
 * Wrapper Class for adding Filters to the ItemXrefManufacturerQuery class
 */
class Mxrfe extends AbstractFilter {
	const MODEL = 'ItemXrefManufacturer';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$columns = [
			Model::aliasproperty('itemid'),
			Model::aliasproperty('mnfrid'),
			Model::aliasproperty('mnfritemid'),
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
		$this->vendoridInput($input);
	}

/* =============================================================
	2. Base Filter Functions
============================================================= */
	/**
	 * Filter the Query on the Vendor ID column
	 * @param  string|array $vendorID      Vendor ID
	 * @param  string       $comparison
	 * @return self
	 */
	public function vendorid($vendorID, $comparison = null) {
		if ($vendorID)  {
			$this->query->filterByVendorid($vendorID, $comparison);
		}
		return $this;
	}

	/**
	 * Filter the Query on the Item ID column
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
	 * Filter the Query on the Vendor ID column
	 * @param  WireInput $input
	 * @return self
	 */
	public function vendoridInput($input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		if ($values->vendorID) {
			$this->vendorid($values->array('vendorID'));
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
