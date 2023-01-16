<?php namespace Dplus\Filters\Misc;
// Dplus Model
use StateCodeQuery, StateCode as Model;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
 * Wrapper Class for StateCodeQuery
 */
class StateCode extends AbstractFilter {
	const MODEL = 'StateCode';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q, $cols = []) {
		$columns = [
			Model::aliasproperty('code'),
			Model::aliasproperty('name'),
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
	public function existsCode($code) {
		return boolval($this->getQueryClass()->filterByCode($code)->count());
	}

	/**
	 * Return if Code Exists
	 * @param  string $code
	 * @return bool
	 */
	public function existsAbbreviation($code) {
		return boolval($this->getQueryClass()->filterByAbbreviation($code)->count());
	}
}
