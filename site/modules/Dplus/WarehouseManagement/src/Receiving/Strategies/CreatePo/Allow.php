<?php namespace Dplus\Wm\Receiving\Strategies\CreatePo;

// ProcessWire
use ProcessWire\WireData;

use Dplus\Wm\Receiving\Strategies\CreatePo\CreatePo as CreateInterface;

/**
 * Allow
 * Strategy for allowing creating Purchase Orders from Receiving
 */
class Allow extends WireData implements CreateInterface {
	const ALLOWED = true;

	public function allowCreatePo() {
		return self::ALLOWED;
	}
}
