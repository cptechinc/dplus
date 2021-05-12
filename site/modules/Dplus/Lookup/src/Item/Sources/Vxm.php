<?php namespace Dplus\Lookup\Item\Sources;
// Propel Classes
use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
//  ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput;

/**
 * Vxm
 * Searches the Database for ItemID against the Vendor X-Ref
 */
class Vxm extends Source  {
	const MODEL = 'ItemXrefVendor';
	const REQUIREDFIELDS = ['vendorid'];
	const SOURCE = 'vxm';

	protected function filterQuery(Query $q) {
		$q->filterByVendorid($this->inputdata->vendorid);
		$q->filterByVendoritemid($this->inputdata->vendoritemid());
	}
}
