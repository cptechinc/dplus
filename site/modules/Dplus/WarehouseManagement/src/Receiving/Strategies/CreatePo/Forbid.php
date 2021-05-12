<?php namespace Dplus\Wm\Receiving\Strategies\CreatePo;

// ProcessWire
use ProcessWire\WireData;

use Dplus\Wm\Receiving\Strategies\CreatePo\CreatePo as CreateInterface;

/**
 * Forbid
 * Strategy for forbidding creating Purchase Orders from Receiving
 */
class Forbid extends WireData implements CreateInterface {
	const ALLOWED = false;

	public function allowCreatePo() {
		return self::ALLOWED;
	}
}
