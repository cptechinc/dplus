<?php namespace Dplus\Filters\Map;
// Dplus Model
use ApBuyerQuery, ApBuyer as Model;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
 * Wrapper Class for adding Filters to the ApBuyerQuery class
 */
class ApBuyer extends CodeFilter {
	const MODEL = 'ApBuyer';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q, $cols = []) {
		$columns = [];
		$cols = array_filter($cols);

		if (empty($cols)) {
			$columns = [
				Model::aliasproperty('id'),
				Model::aliasproperty('description'),
				Model::aliasproperty('email')
			];
			$this->query->searchFilter($columns, strtoupper($q));
			return true;
		}
		foreach ($cols as $col) {
			if (Model::aliasproperty_exists($col)) {
				$columns[] = Model::aliasproperty($col);
			}
		}
		if (empty($columns)) {
			return false;
		}
		$this->query->searchFilter($columns, strtoupper($q));
		return true;
	}
}
