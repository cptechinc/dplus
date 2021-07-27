<?php namespace Dplus\CodeValidators\Min;

use ProcessWire\WireData;

use Propel\Runtime\ActiveQuery\Criteria;

use Dplus\CodeValidators\Min;

use ItemXrefUpcQuery, ItemXrefUpc;

/**
 * Upcx
 * Class for Validating Upcx
 */
class Upcx extends Min {
	/**
	 * Return if UPC exists
	 * @param  string $upc    UPC Code
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function exists($upc, $itemID = '') {
		$q = $this->query();
		$q->filterByUpc($upc);
		if (empty($itemID) === false) {
			$q->filterByItemid($itemID);
		}
		return boolval($q->count());
	}

	/**
	 * Return if Primary UPC for itemID exists
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function primaryExistsForItemid($itemID) {
		$q = $this->query();
		$q->filterByItemid($itemID);
		$q->filterByPrimary(ItemXrefUpc::PRIMARY_TRUE);
		return boolval($q->count());
	}

	/**
	 * Return Query
	 * @return ItemXrefUpcQuery
	 */
	public function query() {
		return ItemXrefUpcQuery::create();
	}
}
