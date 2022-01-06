<?php namespace Dplus\Filters\Min;
// Dplus Model
use MsdsCodeQuery, MsdsCode as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
 * Wrapper Class for MsdsCodeQuery
 */
class MsdsCode extends AbstractFilter {
	const MODEL = 'MsdsCode';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$columns = [
			Model::aliasproperty('id'),
			Model::aliasproperty('description'),
		];
		$this->query->searchFilter($columns, strtoupper($q));
	}
}
