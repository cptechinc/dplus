<?php namespace Dplus\Lookup\Item\Lookups;
// Dplus Model
use PurchaseOrderQuery, PurchaseOrder;

use Dplus\Filters\Mpo\PurchaseOrder as PurchaseOrderFilter;
/**
 * AP Entry
 * Class that searches through sources to get item ID
 * FOR: [PurchaseOrder] Item Entry
 */
class ApEntry extends Base {
	const SOURCES = ['itm', 'cxm-shortitem', 'upcx', 'vxm', 'mxrfe-shortitem'];

	/**
	 * Prepare InputData
	 */
	public function initInputData() {
		if ($this->inputdata->doesFieldHaveValue('ponbr') && $this->inputdata->doesFieldHaveValue('vendorid') === false) {
			$ponbr = $this->wire('sanitizer')->ponbr($this->inputdata->ponbr);
			$filter = new PurchaseOrderFilter();
			$filter->query->select(PurchaseOrder::aliasproperty('vendorid'));
			$filter->query->filterByPonbr($ponbr);

			if ($filter->query->count()) {
				$this->inputdata->vendorid = $filter->query->findOne();
			}
		}
	}
}
