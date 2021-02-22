<?php namespace Dplus\Filters\Mso;

use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
use Dplus\Filters\AbstractFilter;

use SalesHistoryQuery, SalesHistory as Model;

class SalesHistory extends AbstractFilter {
	const MODEL = 'SalesHistory';

/* =============================================================
	Abstract Contract Functions
============================================================= */
	public function initQuery() {
		$this->query = SalesHistoryQuery::create();
	}

	public function _search($q) {
		$columns = [
			Model::get_aliasproperty('ordernumber'),
		];
		$this->query->search_filter($columns, strtoupper($q));
	}

/* =============================================================
	Misc Query Functions
============================================================= */
	/**
	 * Return Position of SalesHistory in results
	 * @param  Model $item SalesHistory
	 * @return int
	 */
	public function position(Model $p) {
		$people = $this->query->find();
		return $people->search($p);
	}

/* =============================================================
	Base Filter Functions
============================================================= */
	/**
	 * Filter Query by Item ID
	 * @param  string $custID Item ID
	 * @return self
	 */
	public function custid($custID) {
		if ($custID) {
			$this->query->filterByItemid($custID);
		}
		return $this;
	}

	/**
	 * Filter Query by Order Number
	 * @param  string $ordn Order Number
	 * @return self
	 */
	public function ordernumber($ordn) {
		if ($ordn) {
			$this->query->filterByOrdernumber($ordn);
		}
		return $this;
	}
}
