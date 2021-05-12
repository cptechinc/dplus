<?php namespace Dplus\Wm\Receiving\Strategies\CreatePo;

// ProcessWire
use ProcessWire\WireData;


/**
 * CreatePo
 * Interface for allowing / forbidding po creation from Receiving
 */
interface CreatePo {
	public function allowCreatePo();
}
