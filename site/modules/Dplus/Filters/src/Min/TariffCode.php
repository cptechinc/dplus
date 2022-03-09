<?php namespace Dplus\Filters\Min;
// Dplus Model
use TariffCodeQuery, TariffCode as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
 * Wrapper Class for TariffCodeQuery
 */
class TariffCode extends CodeFilter {
	const MODEL = 'TariffCode';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$columns = [
			Model::aliasproperty('id'),
			Model::aliasproperty('number'),
			Model::aliasproperty('description'),
		];
		$this->query->searchFilter($columns, strtoupper($q));
	}
}
