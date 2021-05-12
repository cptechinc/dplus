<?php namespace Dplus\Lookup\Item\Sources;
// Propel Classes
use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
//  ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput;

/**
 * Mxrfe
 * Searches the Database for ItemID against the Mnfr X-Ref
 */
class Mxrfe extends Source  {
	const MODEL = 'ItemXrefManufacturer';
	const REQUIREDFIELDS = ['mnfrid'];
	const SOURCE = 'mxrfe';

	protected function filterQuery(Query $q) {
		$q->filterByMnfrid($this->inputdata->mnfrid);
		$q->filterByMnfritemid($this->inputdata->mnfritemid());
	}
}
