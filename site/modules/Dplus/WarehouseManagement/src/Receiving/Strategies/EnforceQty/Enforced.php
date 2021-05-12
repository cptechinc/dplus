<?php namespace Dplus\Wm\Receiving\Strategies\EnforceQty;

// ProcessWire
use ProcessWire\WireData;


/**
 * Enforced
 * Strategy for forcing Qty Received to be Equal or Less than qty ordered
 */
class Enforced extends WireData {
	const ALLOW_OVER_RECEIVE = false;
	const WARN               = true;

	public function allowOverReceive() {
		return self::ALLOW_OVER_RECEIVE;
	}

	public function warn() {
		return self::WARN;
	}
}
