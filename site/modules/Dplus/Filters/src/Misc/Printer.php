<?php namespace Dplus\Filters\Misc;
// Dplus Model
use PrinterQuery, Printer as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
* Wrapper Class for PrinterQuery
*/
class Printer extends AbstractFilter {
	const MODEL = 'Printer';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$columns = [
			Model::get_aliasproperty('id'),
			Model::get_aliasproperty('description'),
			Model::get_aliasproperty('type'),
			Model::get_aliasproperty('typedescription'),
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
