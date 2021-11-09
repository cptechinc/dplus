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
	public function _search($q) {
		$model = $this->modelName();
		$columns = [
			$model::aliasproperty('id'),
			$model::aliasproperty('name'),
			$model::aliasproperty('email'),
		];
		$this->query->searchFilter($columns, strtoupper($q));
	}
/* =============================================================
	2. Base Filter Functions
============================================================= */

/* =============================================================
	3. Input Filter Functions
============================================================= */

/* =============================================================
	4. Misc Query Functions
============================================================= */

}
