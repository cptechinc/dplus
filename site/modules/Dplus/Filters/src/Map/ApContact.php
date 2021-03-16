<?php namespace Dplus\Filters\Map;

use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
use Dplus\Filters\AbstractFilter;

use ApContactQuery, ApContact as Model;

class ApContact extends AbstractFilter {
	const MODEL = 'ApContact';

/* =============================================================
	Abstract Contract Functions
============================================================= */
	public function initQuery() {
		$this->query = ApContactQuery::create();
	}

	public function _search($q) {
		$columns = [
			Model::get_aliasproperty('contactid'),
			Model::get_aliasproperty('title'),
		];
		$this->query->search_filter($columns, strtoupper($q));
	}
}
