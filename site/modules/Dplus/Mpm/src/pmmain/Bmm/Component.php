<?php namespace Dplus\Mpm\Pmmain\Bmm;
// Dplus Models
use BomComponentQuery, BomComponent;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;

class Components extends WireData {
	public function __construct() {
		$this->sessionID = session_id();
	}
}
