<?php namespace Dplus\Wm\Receiving\Strategies\ReadQty;
// ProcessWire
use ProcessWire\WireData;
// Dplus Configs
use Dplus\Configs as Configs;

/**
 * ReadStrategy
 * Strategy for How to Interpret Qtys on Lot Serials
 */
class ReadStrategy extends WireData {
	const TYPE = '';

	static private $PRECISION_QTY;

	public function getQty(float $qty) {
		return 1;
	}

	static public function getPrecision() {
		if (empty(self::$PRECISION_QTY)) {
			$config = Configs\So::config();
			self::$PRECISION_QTY = $config->decimal_places_qty;
		}
		return self::$PRECISION_QTY;
	}
}
