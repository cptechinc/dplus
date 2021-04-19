<?php namespace Dplus\Lookup\Item\Sources;
// Propel Classes
use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveQuery\Criteria;
//  ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput;

/**
 * Itm
 * Searches the Item Master Table for Exact / LIKE item IDs
 */
class Itm extends Source  {
	const MODEL = 'ItemMasterItem';
	const REQUIREDFIELDS = ['itemid'];
	const SOURCE = 'itm';

	protected function filterQuery(Query $q) {
		$q->filterByItemid($this->inputdata->itemid());
	}

	public function countMatches() {
		$q = $this->getQueryClass();
		$q->filterByItemid('%'.$this->inputdata->itemid().'%', Criteria::LIKE);
		return $q->count();
	}
}
