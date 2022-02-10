<?php namespace Dplus\Mar\Arproc;
// ProcessWire
use ProcessWire\WireData;

/**
 * Ecr
 *
 * @property Ecr\Payments Payments
 */
class Ecr extends WireData {
	private static $instance;

	/**
	 * Return Instance
	 * @return self
	 */
	public static function instance() {
		if (empty(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		$this->payments = Ecr\Payments::instance();
	}
}
