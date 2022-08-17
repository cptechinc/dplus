<?php namespace Dplus\Filters\Misc;
// Dplus Model
use CountryCodeQuery, CountryCode as Model;
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
			Model::aliasproperty('iso3'),
			Model::aliasproperty('iso2'),
			Model::aliasproperty('numeric'),
			Model::aliasproperty('description'),
		];
		$this->query->searchFilter($columns, strtoupper($q));
	}

/* =============================================================
	4. Misc Query Functions
============================================================= */
	/**
	 * Return if Code Exists
	 * @param  string $code
	 * @return bool
	 */
	public function existsIso3($code) {
		return boolval($this->getQueryClass()->filterByIso3($code)->count());
	}
}
