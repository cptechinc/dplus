<?php namespace Dplus\Filters\Map;
// Dplus Model
use ApContactQuery, ApContact as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
 * Wrapper Class for adding Filters to the ApContactQuery class
 */
class ApContact extends AbstractFilter {
	const MODEL = 'ApContact';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$columns = [
			Model::get_aliasproperty('contactid'),
			Model::get_aliasproperty('title'),
		];
		$this->query->search_filter($columns, strtoupper($q));
	}
}
