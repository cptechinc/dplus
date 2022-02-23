<?php namespace Dplus\Filters\Min;
// Dplus Model
use MsdsCodeQuery, MsdsCode as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
 * Wrapper Class for MsdsCodeQuery
 */
class MsdsCode extends CodeFilter {
	const MODEL = 'MsdsCode';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$columns = [
			Model::aliasproperty('id'),
			Model::aliasproperty('description'),
			Model::aliasproperty('effectivedate'),
		];
		$this->query->searchFilter($columns, strtoupper($q));
	}
}
