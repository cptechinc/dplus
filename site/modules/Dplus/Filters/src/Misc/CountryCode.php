<?php namespace Dplus\Filters\Misc;
// Dplus Model
use CountryCodeQuery, CountryCode as Model;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
 * Wrapper Class for CountryCodeQuery
 */
class CountryCode extends CodeFilter {
	const MODEL = 'CountryCode';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q, $cols = []) {
		$model = $this->modelName();
		$columns = [];
		$cols = array_filter($cols);

		if (empty($cols)) {
			$columns = [
				Model::aliasproperty('iso3'),
				Model::aliasproperty('iso2'),
				Model::aliasproperty('numeric'),
				Model::aliasproperty('description'),
			];
			$this->query->searchFilter($columns, strtoupper($q));
			return true;
		}

		foreach ($cols as $col) {
			if ($model::aliasproperty_exists($col)) {
				$columns[] = $model::aliasproperty($col);
			}
		}
		if (empty($columns)) {
			return false;
		}
		$this->query->searchFilter($columns, strtoupper($q));
		return true;
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
