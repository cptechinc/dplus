<?php namespace Dplus\Lookup\Item\Sources;
// Propel Classes
use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
//  ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput;

/**
 * Cxm
 * Searches the Database for ItemID against the Customer X-Ref
 */
class Cxm extends Source  {
	const MODEL = 'ItemXrefCustomer';
	const REQUIREDFIELDS = ['custid'];
	const SOURCE = 'cxm';

	protected function filterQuery(Query $q) {
		$q->filterByCustid($this->inputdata->custid);
		$q->filterByCustitemid($this->inputdata->custitemid());
	}
}
