<?php namespace Dplus\Wm\Sop\Picking\Strategies\Inventory\Lookup;

use Dplus\Wm\Base;

use WhseitemphysicalcountQuery, Whseitemphysicalcount;

/**
 * Lookup
 * Base Inventory Lookup Class
 */
abstract class Lookup extends Base {
	/**
	 * Return Query filtered by Session ID
	 * @return WhseitemphysicalcountQuery
	 */
	public function query() {
		$q = WhseitemphysicalcountQuery::create();
		$q->filterBySessionid($this->sessionID);
		return $q;
	}

	/**
	 * Return Query for Inventory Items
	 * @param  string $scan        Scan
	 * @param  bool   $includepack Include PACK bin?
	 * @return WhseitemphysicalcountQuery
	 */
	public function getScanQuery($scan) {
		$q = $this->query();
		$q->filterByScan($scan);
		return $q;
	}

	/**
	 * Return Inventory Items
	 * @param  string $scan        Scan
	 * @param  bool   $includepack Include Pack Bin?
	 * @return Whseitemphysicalcount[]|ObjectCollection
	 */
	public function getResults($scan) {
		$q = $this->getScanQuery($scan);
		return $q->find();
	}
}
