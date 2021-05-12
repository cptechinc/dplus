<?php namespace Dplus\Lookup\Item\Sources;
// Propel Classes
use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
//  ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Configs
use Dplus\Configs\Sys as SysConfig;

/**
 * CxmShortItem
 * Searches the Database for ItemID against the Customer X-Ref, using sysconfig's customer id
 */
class CxmShortItem extends Source  {
	const MODEL = 'ItemXrefCustomer';
	const REQUIREDFIELDS = ['itemid'];
	const SOURCE = 'cxm-shortitem';

	protected function filterQuery(Query $q) {
		$config = SysConfig::config();

		$q->filterByCustid($config->custid);
		$q->filterByCustitemid($this->inputdata->itemid());
	}
}
