<?php namespace Dplus\Lookup\Item\Lookups;
// Dplus Model
use SalesOrderQuery;

use Dplus\Filters\Mso\SalesOrder as SalesOrderFilter;
/**
 * AR Entry
 * Class that searches through sources to get item ID
 * FOR: [SalesOrder|Cart|Quote] Item Entry
 */
class ArEntry extends Base {
	const SOURCES = ['itm', 'cxm-shortitem', 'upcx', 'cxm'];

	/**
	 * Prepare InputData
	 */
	public function initInputData() {
		if ($this->inputdata->doesFieldHaveValue('ordn') && $this->inputdata->doesFieldHaveValue('custid') === false) {
			$ordn = $this->wire('sanitizer')->ordn($this->inputdata->ordn);
			$filter = new SalesOrderFilter();
			$filter->query->select(SalesOrder::aliasproperty('ordn'));
			$filter->query->filterByOrdernumber($ordn);
			
			if ($filter->query->count()) {
				$this->inputdata->custid = $filter->query->findOne();
			}
		}
	}
}
