<?php namespace Dplus\Lookup\Item\Sources;
// Propel Classes
use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveQuery\Criteria;
//  ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput;

/**
 * Upcx
 * Searches the Database for ItemID against the UPC X-Ref
 */
class Upcx extends Source  {
	const MODEL = 'ItemXrefUpc';
	const REQUIREDFIELDS = [];
	const SOURCE = 'upcx';

	protected function filterQuery(Query $q) {
		$q->filterByItemid($this->inputdata->upc());
	}

	public function countMatches() {
		$q = $this->getQueryClass();
		$q->filterByUpc('%'.$this->inputdata->upc().'%', Criteria::LIKE);
		return $q->count();
	}
}
