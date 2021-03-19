<?php namespace Dplus\Filters\Mgl;
// Propel Classes
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Model
use GlCodeQuery, GlCode as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
 * Wrapper Class for adding Filters to the GlCodeQuery class
 */
class GlCode extends AbstractFilter {
	const MODEL = 'GlCode';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$columns = [
			Model::aliasproperty('id'),
			Model::aliasproperty('description'),
		];
		$this->query->search_filter($columns, strtoupper($q));
	}
}
