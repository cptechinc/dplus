<?php namespace Dplus\Filters\Mth;
// Dplus Model
use ThermalLabelFormatQuery, ThermalLabelFormat as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
* Wrapper Class for ThermalLabelFormatQuery
*/
class ThermalLabelFormat extends AbstractFilter {
	const MODEL = 'ThermalLabelFormat';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$columns = [
			Model::get_aliasproperty('id'),
			Model::get_aliasproperty('description'),
			Model::get_aliasproperty('width'),
			Model::get_aliasproperty('length'),
		];
		$this->query->searchFilter($columns, strtoupper($q));
	}

/* =============================================================
	2. Base Filter Functions
============================================================= */

/* =============================================================
	3. Input Filter Functions
============================================================= */

}
