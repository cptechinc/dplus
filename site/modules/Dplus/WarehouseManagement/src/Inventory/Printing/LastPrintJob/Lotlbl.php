<?php namespace Dplus\Wm\LastPrintJob;
// ProcessWire
use ProcessWire\WireData;
// Dplus Wm
use Dplus\Wm\LastPrintJob;

class Lotlbl extends LastPrintJob {
	const JOBCODE = 'lotlbl';

	private static $instance;

	public static function getInstance() {
		if (empty(self::$instance)) {
			$instance = new self();
			self::$instance = $instance;
		}
		return self::$instance;
	}
}
