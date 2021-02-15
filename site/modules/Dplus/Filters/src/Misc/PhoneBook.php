<?php namespace Dplus\Filters\Misc;

use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
use Dplus\Filters\AbstractFilter;

use PhoneBookQuery, PhoneBook as PhoneBookModel;

class PhoneBook extends AbstractFilter {
	const MODEL = 'PhoneBook';

/* =============================================================
	Abstract Contract Functions
============================================================= */
	public function initQuery() {
		$this->query = PhoneBookQuery::create();
	}

	public function _search($q) {
		$columns = [
			PhoneBookModel::get_aliasproperty('key1'),
			PhoneBookModel::get_aliasproperty('key2'),
			PhoneBookModel::get_aliasproperty('contact'),
			PhoneBookModel::get_aliasproperty('phone'),
			PhoneBookModel::get_aliasproperty('fax'),
		];
		$this->query->search_filter($columns, strtoupper($q));
	}

	/**
	 * Filter Query with Input Data
	 * @param  WireInput $input Input Data
	 * @return self
	 */
	public function _filterInput(WireInput $input) {
		$this->filterInputVendorid($input);
	}

/* =============================================================
	Filter Functions
============================================================= */
	public function filterInputVendorid(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$vendorID = $values->array('vendorID', 'text', ['delimiter' => ',']);
		$this->filterVendorid($vendorID);
	}

	public function filterVendorid($vendorID) {
		if ($vendorID) {
			$this->query->filterByType([PhoneBookModel::TYPE_VENDOR, PhoneBookModel::TYPE_VENDORCONTACT]);
			$this->query->filterByVendorid($vendorID);
		}
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
}
