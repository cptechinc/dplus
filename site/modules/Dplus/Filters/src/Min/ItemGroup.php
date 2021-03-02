<?php namespace Dplus\Filters\Min;

use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
use Dplus\Filters\AbstractFilter;

use InvGroupCodeQuery, InvGroupCode as InvGroupCodeClass;

class ItemGroup extends AbstractFilter {
	const MODEL = 'InvGroupCode';

/* =============================================================
	Abstract Contract Functions
============================================================= */
	public function initQuery() {
		$this->query = InvGroupCodeQuery::create();
	}

	public function _search($q) {
		$columns = [
			InvGroupCodeClass::get_aliasproperty('code'),
			InvGroupCodeClass::get_aliasproperty('description'),
		];
		$this->query->search_filter($columns, strtoupper($q));
	}

/* =============================================================
	Misc Query Functions
============================================================= */
	/**
	 * Return Position of InvGroupCode in results
	 * @param  InvGroupCodeClass $item InvGroupCode
	 * @return int
	 */
	public function position(InvGroupCodeClass $p) {
		$people = $this->query->find();
		return $people->search($p);
	}
}
