<?php namespace Dplus\Filters\Min;
// Propel Classes
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Model
use UserPermissionsItmQuery, UserPermissionsItm as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
 * Wrapper Class for UserPermissionsItmQuery
 */
class UserPermissionsItm extends CodeFilter {
	const MODEL = 'UserPermissionsItm';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q, $cols = []) {
		$columns = array(
			Model::aliasproperty('userid'),
		);
		$this->query->searchFilter($columns, strtoupper($q));
	}

/* =============================================================
	Base Filter Functions
============================================================= */

/* =============================================================
	Input Functions
============================================================= */


/* =============================================================
	Misc Query Functions
============================================================= */

}
