<?php namespace Dplus\Filters\Msa;
// Dplus Model
use DplusUserQuery, DplusUser as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
* Wrapper Class for DplusUserQuery
*/
class DplusUser extends CodeFilter {
	const MODEL = 'DplusUser';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q, $cols = []) {
		$cols = array_filter($cols);
		$columns = [
			Model::aliasproperty('id'),
			Model::aliasproperty('name'),
			Model::aliasproperty('email'),
			Model::aliasproperty('whseid'),
		];

		if (empty($cols)) {
			$this->query->searchFilter($columns, strtoupper($q));
			return true;
		}

		$columns = [];
		foreach ($cols as $col) {
			if (Model::aliasproperty_exists($col)) {
				$columns[] = Model::aliasproperty($col);
			}
		}
		if (empty($columns)) {
			return false;
		}
		$this->query->searchFilter($columns, strtoupper($q));
		return true;
	}
	
/* =============================================================
	2. Base Filter Functions
============================================================= */
	/**
	 * Filter query by user ID
	 * @param  string       $id          User ID
	 * @param  string|null  $comparison
	 * @return void
	 */
	public function userid($id, $comparison = null) {
		$this->query->filterByUserid($id, $comparison);
	}
	
/* =============================================================
	3. Input Filter Functions
============================================================= */

/* =============================================================
	4. Misc Query Functions
============================================================= */

}
