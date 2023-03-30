<?php namespace Dplus\Msa\Logm\Password;
// Dplus Models
use DplusUser;
// ProcessWire
use ProcessWire\WireInput;


class WebPassword extends Password {
	const FIELD_ATTRIBUTES = [];

	private static $instance;

	public static function getInstance() {
		if (empty(self::$instance)) {
			$instance = new self();
			self::$instance = $instance;
		}
		return self::$instance;
	}
}
