<?php namespace Dplus\CodeValidators;

use ProcessWire\WireData;

use Propel\Runtime\ActiveQuery\Criteria;

use Dplus\CodeValidators\Mki;

use InvKitQuery, InvKit;
use InvKitComponent, InvHazmatItem;
use SalesOrderDetailQuery, SalesOrderDetail;

/**
 * Kim
 *
 * Class for Validating Mki Code Tables / IDs
 */
class Kim extends Mki {
/* =============================================================
	Kim Functions
============================================================= */
	/**
	 * Return if Kit  Exists
	 * @param  string $kitID  Kit ID
	 * @return bool
	 */
	public function kit($kitID) {
		return boolval(InvKitQuery::create()->filterByItemid($kitID)->count());
	}

	/**
	 * Return if Kit Component Exists
	 * @param  string $kitID      Kit ID
	 * @param  string $component  component Item ID
	 * @return bool
	 */
	public function kit_component($kitID, $component) {
		return boolval(InvKitComponentQuery::create()->filteryByKitid($kitID)->filterByItemid($component)->count());
	}

	/**
	 * Validate Freegoods Value
	 * @param  string $val Free Goods
	 * @return bool
	 */
	public function component_freegoods($val) {
		return in_array($val, ['Y', 'N']);
	}

	/**
	 * Validate Component Supplied By
	 * @param  string $val Supplied By
	 * @return bool
	 */
	public function component_suppliedby($val) {
		return array_key_exists($val, InvKitComponent::OPTIONS_SUPPLIEDBY);
	}

	/**
	 * Validate Component Usage Tag
	 * @param  string $val Usage Tag
	 * @return bool
	 */
	public function component_usagetag($val) {
		return array_key_exists($val, InvKitComponent::OPTIONS_USAGETAG);
	}

	/**
	 * Return if Kit can deleted
	 * @param  string $kitID Kit Item ID
	 * @return bool
	 */
	public function can_delete($kitID) {
		return $this->is_ordered($kitID) === false;
	}

	/**
	 * Return If Kit Item exists on Order
	 * @param  string $kitID Kit Item ID
	 * @return bool
	 */
	public function is_ordered($kitID) {
		return boolval(SalesOrderDetailQuery::create()->filterByItemid($kitID)->count());
	}
}
