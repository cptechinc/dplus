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

/* =============================================================
	CRUD Processing
============================================================= */
	/**
	 * Process Input, Act on action needed
	 * @param  WireInput $input Input Data
	 * @return void
	 */
	public function processInput(WireInput $input) {
		$rm     = strtolower($input->requestMethod());
		$values = $input->$rm;

		switch ($values->text('action')) {
			case 'update-component':
			case 'delete-component':
				$this->components->processInput($input);
				break;
		}
	}

/* =============================================================
	BMM Response
============================================================= */
	/**
	 * Return Response
	 * @return Response
	 */
	public static function getResponse() {
		$d = new WireData();
		return $d->wire('session')->getFor('response', 'bmm');
	}

	/**
	 * Delete Response
	 */
	public static function deleteResponse() {
		$d = new WireData();
		$d->wire('session')->removeFor('response', 'bmm');
	}

	/**
	 * Set Response
	 * @param Response $response
	 */
	public static function setResponse($response) {
		$d = new WireData();
		return $d->wire('session')->setFor('response', 'bmm', $response);
	}

/* =============================================================
	RecordLocker
============================================================= */
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

/* =============================================================
	Supplemental
============================================================= */
	/**
	 * Return Pm Config
	 * @return ConfigPm
	 */
	public function getConfigPm() {
		return Configs\Pm::config();
	}
}
