<?php namespace Dplus\Filters\Misc;
// Dplus Model
use CountryCodeQuery, CountryCode as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
 * Wrapper Class for CountryCodeQuery
 */
class CountryCode extends AbstractFilter {
	const MODEL = 'CountryCode';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$columns = [
			CountryCode::aliasproperty('iso3'),
			CountryCode::aliasproperty('iso2'),
			CountryCode::aliasproperty('numeric'),
			CountryCode::aliasproperty('description'),
		];
		$this->query->search_filter($columns, strtoupper($q));
	}
}
