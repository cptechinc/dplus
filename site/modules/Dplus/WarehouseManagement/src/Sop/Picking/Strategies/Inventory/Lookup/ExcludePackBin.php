<?php namespace Dplus\Wm\Sop\Picking\Strategies\Inventory\Lookup;
// Propel ORM Library
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Wm
use Dplus\Wm\Sop\Picking\Strategies\Inventory\Lookup\Lookup as Base;

/**
 * ExcludePackBin
 * Strategy for Inventory Lookup that Excludes looking in Pack Bin
 */
class ExcludePackBin extends Base {
	/**
	 * Return Query for Inventory Items
	 * @param  string $scan        Scan
	 * @param  bool   $includepack Include PACK bin?
	 * @return WhseitemphysicalcountQuery
	 */
	public function getScanQuery($scan) {
		$q = $this->query();
		$q->filterByScan($scan);
		$q->filterByBin('PACK', Criteria::ALT_NOT_EQUAL);
		return $q;
	}
}
