<?php namespace Dplus\Wm\Sop\Picking\Strategies\PackBin;

use ProcessWire\WireData;

/**
 * PackBin
 * Base Inventory Lookup Class
 */
abstract class PackBin extends WireData {
	const INCLUDED = false;

	/**
	 * Return if Pack Bin should be included
	 * @return bool
	 */
	public function includePackBin(){
		return static::INCLUDED;
	}
}
