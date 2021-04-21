<?php namespace Dplus\Wm\Receiving\Strategies\EnforcePoItemids;

// ProcessWire
use ProcessWire\WireData;

use EnforcePoItemids as EnforceInterface;

/**
 * Enforced
 * Strategy for forcing only PO Itemids to be be received
 */
class Enforced extends WireData implements EnforceInterface {
	const ALLOW_ITEMS_NOT_LISTED  = false;

	public function allowItemsNotListed() {
		return self::ALLOW_ITEMS_NOT_LISTED;
	}
}
