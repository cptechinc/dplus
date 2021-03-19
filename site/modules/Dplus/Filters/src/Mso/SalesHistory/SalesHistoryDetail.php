<?php namespace Dplus\Filters\Mso\SalesHistory;
// Dplus Model
use SalesHistoryQuery, SalesHistory;
use SalesHistoryDetailQuery, SalesHistoryDetail as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;
use Dplus\Filters\Mso\SalesHistory as SalesHistoryFilter;

/**
 * Wrapper Class for SalesHistoryDetailQuery
 */
class Detail extends AbstractFilter {
	const MODEL = 'SalesHistoryDetail';

/* =============================================================
	1. Abstract Contract Functions
============================================================= */
	public function _search($q) {
		$columns = [
			Model::aliasproperty('code'),
			Model::aliasproperty('description'),
		];
		$this->query->search_filter($columns, strtoupper($q));
	}
	
/* =============================================================
	2. Base Filter Functions
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

	/**
	 * Filter To Customer's By Getting their Sales Order Numbers from Sales History
	 * @param  string $custID Customer ID
	 * @return self
	 */
	public function filterCustomerHistory($custID) {
		$q = SalesHistoryQuery::create();
		$q->filterByCustid($custID);
		$q->select(SalesHistory::aliasproperty('ordernumber'));
		$ordn = $q->find()->toArray();

		$this->ordernumber($ordn);
		$this->query->sortBy('oedhyear', 'DESC');
		return $this;
	}
}
