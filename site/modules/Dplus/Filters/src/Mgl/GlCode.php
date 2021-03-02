<?php namespace Dplus\Filters\Mgl;

use Propel\Runtime\ActiveQuery\Criteria;
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
use Dplus\Filters\AbstractFilter;

use GlCodeQuery, GlCode as GlCodeClass;

class GlCode extends AbstractFilter {
	const MODEL = 'GlCode';

/* =============================================================
	Abstract Contract Functions
============================================================= */
	public function initQuery() {
		$this->query = GlCodeQuery::create();
	}

	public function _search($q) {
		$columns = [
			GlCodeClass::aliasproperty('id'),
			GlCodeClass::aliasproperty('description'),
		];
		$this->query->search_filter($columns, strtoupper($q));
	}

/* =============================================================
	Misc Query Functions
============================================================= */
	/**
	 * Return Position of GlCode in results
	 * @param  GlCodeClass $item GlCode
	 * @return int
	 */
	public function position(GlCodeClass $p) {
		$people = $this->query->find();
		return $people->search($p);
	}

/* =============================================================
	Filter Input Functions
============================================================= */

/* =============================================================
	Base Filter Functions
============================================================= */
}
