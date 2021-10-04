<?php namespace Dplus\Mpm\Pmmain;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as FunctionLocker;
// Dplus Configs
use Dplus\Configs;
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
	 * Lock Record
	 * @param  string $bomID BoM Header Item ID
	 * NOTE: Keep public so it can be used by Itm\Xrefs\Bom
	 * @return bool
	 */
	public function lockrecord($bomID) {
		if ($this->header->exists($bomID) === false) {
			return false;
		}
		if ($this->recordlocker->islocked($bomID) && $this->recordlocker->userHasLocked($bomID) === false) {
			return false;
		}
		if ($this->recordlocker->userHasLocked($bomID)) {
			return true;
		}
		return $this->recordlocker->lock($bomID);
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

	public function getConfigPm() {
		return Configs\Pm::config();
	}
}
