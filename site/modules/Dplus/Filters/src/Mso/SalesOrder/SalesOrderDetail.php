<?php namespace Dplus\Filters\Mso\SalesOrder;
// Dplus Model
use SalesOrderDetailQuery, SalesOrderDetail as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
 * Wrapper Class for SalesOrderDetailQuery
 */
class SalesOrderDetail extends AbstractFilter {
	const MODEL = 'SalesOrderDetail';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$columns = [
			Model::aliasproperty('code'),
			Model::aliasproperty('description'),
		];
		$this->query->search_filter($columns, strtoupper($q));
	}
}
