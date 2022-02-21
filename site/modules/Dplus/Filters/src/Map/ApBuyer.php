<?php namespace Dplus\Filters\Map;
// Propel Classes
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Model
use ApBuyerQuery, ApBuyer as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
 * Wrapper Class for adding Filters to the ApBuyerQuery class
 */
class ApBuyer extends CodeFilter {
	const MODEL = 'ApBuyer';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$columns = [
			Model::get_aliasproperty('id'),
			Model::get_aliasproperty('description'),
			Model::get_aliasproperty('email'),
		];
		$this->query->searchFilter($columns, strtoupper($q));
	}
}
