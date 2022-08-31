<?php namespace Dplus\Filters\Map;
// Propel Classes
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Model
use ApBuyerQuery, ApBuyer as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
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
		$model = $this->modelName();
		$columns = [];
		$cols = array_filter($cols);

		if (empty($cols)) {
			$columns = [
				$model::aliasproperty('id'),
				$model::aliasproperty('description'),
				$model::aliasproperty('email')
			];
	
			$this->query->searchFilter($columns, strtoupper($q));
			return true;
		}
		foreach ($cols as $col) {
			if ($model::aliasproperty_exists($col)) {
				$columns[] = $model::aliasproperty($col);
			}
			$this->query->searchFilter($columns, strtoupper($q));
			return true;
		}
	}
}
