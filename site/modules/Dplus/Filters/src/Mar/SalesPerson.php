<?php namespace Dplus\Filters\Mar;

use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
use Dplus\Filters\AbstractFilter;

use SalesPersonQuery, SalesPerson as SalesPersonClass;

class SalesPerson extends AbstractFilter {
	const MODEL = 'SalesPerson';

/* =============================================================
	Abstract Contract Functions
============================================================= */
	public function initQuery() {
		$this->query = SalesPersonQuery::create();
	}

	public function _search($q) {
		$columns = [
			SalesPersonClass::get_aliasproperty('contactid'),
			SalesPersonClass::get_aliasproperty('title'),
		];
		$this->query->search_filter($columns, strtoupper($q));
	}

/* =============================================================
	Misc Query Functions
============================================================= */
	/**
	 * Return Position of SalesPerson in results
	 * @param  SalesPersonClass $item SalesPerson
	 * @return int
	 */
	public function position(SalesPersonClass $p) {
		$people = $this->query->find();
		return $people->search($p);
	}
}
