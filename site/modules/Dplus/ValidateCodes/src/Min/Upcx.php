<?php namespace Dplus\CodeValidators\Min;

use ProcessWire\WireData;

use Propel\Runtime\ActiveQuery\Criteria;

use Dplus\CodeValidators\Min;
use Dplus\CodeValidators\Map as MapValidator;
use Dplus\CodeValidators\Mar as MarValidator;
use Dplus\CodeValidators\Mso as MsoValidator;

/**
 * IUpcx
 *
 * Class for Validating Upcx
 */
class Upcx extends Min {
	/**
	 * Return if UPC exists
	 * @param  string $upc UPC Code
	 * @return bool
	 */
	public function exists($upc) {
		$q = ItemXrefUpcQuery::create();
		$q->filterByUpc($upc);
		return boolval($q->count());
	}
}
