<?php namespace Dplus\Wm\Receiving\Strategies\EnforceQty;

// ProcessWire
use ProcessWire\WireData;


/**
 * Relaxed
 * Strategy for allowing Qty Received to be more than qty ordered
 */
class Warn extends WireData {
	const ALLOW_OVER_RECEIVE = true;
	const WARN               = true;

	public function allowOverReceive() {
		return self::ALLOW_OVER_RECEIVE;
	}

	public function warn() {
		return self::WARN;
	}
}
