<?php namespace Dplus\Filters\Min;
// Dplus Model
use InvGroupCodeQuery, InvGroupCode as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
 * Wrapper Class for adding Filters to the InvGroupCodeQuery class
 */
class ItemGroup extends AbstractFilter {
	const MODEL = 'InvGroupCode';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$columns = [
			Model::aliasproperty('code'),
			Model::aliasproperty('description'),
		];
		$this->query->search_filter($columns, strtoupper($q));
	}
}
