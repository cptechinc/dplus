<?php namespace Dplus\Wm\Receiving\Strategies\ReadQty;

use Dplus\Configs as Configs;

/**
 * LotserialQty
 * Strategy for Receiving Lotserials with the Qty provided
 */
class LotserialQty extends ReadStrategy {
	const TYPE = 'qty';

	public function getQty(float $qty) {
		return $sanitizer->float($qty, ['precision' => self::getPrecision()]);
	}
}
