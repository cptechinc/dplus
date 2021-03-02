<?php namespace Dplus\Filters\Mso\SalesOrder;

use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
use Dplus\Filters\AbstractFilter;

use SalesOrderDetailQuery, SalesOrderDetail as Model;

class SalesOrderDetail extends AbstractFilter {
	const MODEL = 'SalesOrderDetail';

/* =============================================================
	Abstract Contract Functions
============================================================= */
	public function initQuery() {
		$this->query = SalesOrderDetailQuery::create();
	}

	public function _search($q) {
		$columns = [
			Model::get_aliasproperty('code'),
			Model::get_aliasproperty('description'),
		];
		$this->query->search_filter($columns, strtoupper($q));
	}

/* =============================================================
	Misc Query Functions
============================================================= */
	/**
	 * Return Position of SalesOrderDetail in results
	 * @param  Model $item SalesOrderDetail
	 * @return int
	 */
	public function position(Model $p) {
		$people = $this->query->find();
		return $people->search($p);
	}
}
