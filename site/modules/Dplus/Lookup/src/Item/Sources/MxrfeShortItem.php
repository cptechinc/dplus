<?php namespace Dplus\Lookup\Item\Sources;
// Propel Classes
use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
//  ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Configs
use Dplus\Configs\Sys as SysConfig;

/**
 * Mxrfe
 * Searches the Database for ItemID against the Mnfr X-Ref using using sysconfig's customer id as Mnfr ID
 */
class MxrfeShortItem extends Source  {
	const MODEL = 'ItemXrefManufacturer';
	const REQUIREDFIELDS = [];
	const SOURCE = 'mxrfe-shortitem';

	protected function filterQuery(Query $q) {
		$config = SysConfig::config();
		$this->inputdata->mnfrid = $config->custid;

		$q->filterByMnfrid($this->inputdata->mnfrid);
		$q->filterByMnfritemid($this->inputdata->mnfritemid());
	}
}
