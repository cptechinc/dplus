<?php namespace Dplus\Mpm\Pmmain;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as FunctionLocker;
// Dplus CRUD
use Dplus\Mpm\Pmmain\Bom;

/**
 * CRUD Wrapper for BMM header, components
 */
class Bmm extends WireData {
	const RECORDLOCKER_FUNCTION = 'bom';

	public function __construct() {
		$this->sessionID   = session_id();
		$this->header      = new Bmm\Header();
		$this->components  = new Bmm\Components();

		$this->initRecordlocker();
	}

	/**
	 * Intialize Record Locker
	 */
	public function initRecordlocker() {
		$this->recordlocker = self::getRecordLocker();
	}

	/**
	 * Return Record Locker
	 * @return FunctionLocker
	 */
	public static function getRecordLocker() {
		$locker = new FunctionLocker();
		$locker->setFunction(self::RECORDLOCKER_FUNCTION);
		$locker->setUser($locker->wire('user'));
		return $locker;
	}
}