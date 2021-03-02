<?php namespace Dplus\Filters\Mso\SalesHistory;

use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
use Dplus\Filters\AbstractFilter;

use SalesHistoryQuery, SalesHistory;
use SalesHistoryDetailQuery, SalesHistoryDetail as Model;
use Dplus\Filters\Mso\SalesHistory as SalesHistoryFilter;

class Detail extends AbstractFilter {
	const MODEL = 'SalesHistoryDetail';

/* =============================================================
	Abstract Contract Functions
============================================================= */
	public function initQuery() {
		$this->query = SalesHistoryDetailQuery::create();
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
	 * Return Position of SalesHistoryDetail in results
	 * @param  Model $item SalesHistoryDetail
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
	 * @param  string $itemID Item ID
	 * @return self
	 */
	public function itemid($itemID) {
		if ($itemID) {
			$this->query->filterByItemid($itemID);
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

/* =============================================================
	Base Filter Functions
============================================================= */
	public function filterCustomerHistory($custID) {
		$q = SalesHistoryQuery::create();
		$q->filterByCustid($custID);
		$q->select(SalesHistory::aliasproperty('ordernumber'));
		$ordn = $q->find()->toArray();

		$this->ordernumber($ordn);
		$this->query->sortBy('oedhyear', 'DESC');
	}
}
