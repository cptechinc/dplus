<?php namespace Dplus\Wm\Receiving\Strategies\EnforceQty;

// ProcessWire
use ProcessWire\WireData;


/**
 * Relaxed
 * Strategy for allowing Qty Received to be more than qty ordered
 */
class Relaxed extends WireData {
	const ALLOW_OVER_RECEIVE = true;
	const WARN               = false;

	public function allowOverReceive() {
		return self::ALLOW_OVER_RECEIVE;
	}

	public function warn() {
		return self::WARN;
	}
}
