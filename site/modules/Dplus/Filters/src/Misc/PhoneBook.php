<?php namespace Dplus\Filters\Misc;
// Dplus Model
use PhoneBookQuery, PhoneBook as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
* Wrapper Class for PhoneBookQuery
*/
class PhoneBook extends AbstractFilter {
	const MODEL = 'PhoneBook';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$columns = [
			Model::get_aliasproperty('key1'),
			Model::get_aliasproperty('key2'),
			Model::get_aliasproperty('contact'),
			Model::get_aliasproperty('phone'),
			Model::get_aliasproperty('fax'),
		];
		$this->query->search_filter($columns, strtoupper($q));
	}

	/**
	 * Filter Query with Input Data
	 * @param  WireInput $input Input Data
	 * @return self
	 */
	public function _filterInput(WireInput $input) {
		$this->filterVendoridInput($input);
	}

/* =============================================================
	2. Base Filter Functions
============================================================= */
	/**
	 * Filter the Query By Vendor ID
	 * @param  string $vendorID Vendor ID
	 * @return self
	 */
	public function filterVendorid($vendorID) {
		if ($vendorID) {
			$this->query->filterByType([Model::TYPE_VENDOR, Model::TYPE_VENDORCONTACT]);
			$this->query->filterByVendorid($vendorID);
		}
		return $this
	}

	/**
	 * Filter Query By Type
	 * @param  string $type Type e.g  VC | CC
	 * @return self
	 */
	public function type($type = '') {
		if ($type) {
			$this->query->filterByType($type);
		}
		return $this;
	}

/* =============================================================
	3. Input Filter Functions
============================================================= */
	/**
	 * Filter the Query By Vendor Id using Input Data
	 * @param  WireInput $input Input Data
	 * @return self
	 */
	public function filterVendoridInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$vendorID = $values->array('vendorID', 'text', ['delimiter' => ',']);
		$this->filterVendorid($vendorID);
		return $this;
	}
}
