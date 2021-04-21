<?php namespace Dplus\Wm\Receiving\Strategies\EnforcePoItemids;

// ProcessWire
use ProcessWire\WireData;

use Dplus\Wm\Receiving\Strategies\EnforcePoItemids\EnforcePoItemids as EnforceInterface;

/**
 * Relaxed
 * Strategy for allowing ANY Item ID to be received
 */
class Relaxed extends WireData implements EnforceInterface {
	const ALLOW_ITEMS_NOT_LISTED  = true;

	public function allowItemsNotListed() {
		return self::ALLOW_ITEMS_NOT_LISTED;
	}
}
