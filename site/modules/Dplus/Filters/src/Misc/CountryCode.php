<?php namespace Dplus\Filters\Misc;

use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
use Dplus\Filters\AbstractFilter;

use CountryCodeQuery, CountryCode as CountryCodeModel;

class CountryCode extends AbstractFilter {
	const MODEL = 'CountryCode';

/* =============================================================
	Abstract Contract Functions
============================================================= */
	public function initQuery() {
		$this->query = CountryCodeQuery::create();
	}

	public function _search($q) {
		$columns = [
			CountryCode::get_aliasproperty('iso3'),
			CountryCode::get_aliasproperty('iso2'),
			CountryCode::get_aliasproperty('numeric'),
			CountryCode::get_aliasproperty('description'),
		];
		$this->query->search_filter($columns, strtoupper($q));
	}

	/**
	 * Filter Query with Input Data
	 * @param  WireInput $input Input Data
	 * @return self
	 */
	public function _filterInput(WireInput $input) {

	}

/* =============================================================
	Filter Functions
============================================================= */

}
