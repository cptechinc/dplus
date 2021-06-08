<?php namespace Dplus\RecordLocker;

use Propel\Runtime\ActiveQuery\Criteria;
use LockRecordQuery, LockRecord;

use ProcessWire\WireData, ProcessWire\User;

/**
 * Locker
 *
 * Class for Creating, Reading LockRecord for the purposes of Dplus
 *
 * NOTE: Examples provided will be for IWHM
 */
class UserFunction extends WireData {
	private static $locker;
	private $function;
	private $user;

	public function __construct() {
		$this->user = $this->wire('user');
	}

	public function setFunction($function) {
		$this->function = $function;
		return $this;
	}

	public function getFunction() {
		return $this->function;
	}

	public function setUser(User $user) {
		$this->user = $user;
		return $this;
	}

	public function getUserid() {
		return $this->user->loginid;
	}

	/**
	 * Return LockRecordQuery
	 * @return Locker
	 */
	private static function locker() {
		if (empty(self::$locker)) {
			self::$locker = new Locker();
		}
		return self::$locker;
	}

	/**
	 * Returns if Function is being locked by User ID
	 * @param  mixed  $key       ID / Key of what is being locked in Function e.g. IWHM warehouse ID
	 * @return bool
	 */
	public function userHasLocked($key) {
		return self::locker()->userHasRecordLocked($this->function, $key, $this->user->loginid);
	}

	/**
	 * Returns if Function is being locked
	 * @param  string $key       ID / Key of what is being locked in Function e.g. IWHM warehouse ID
	 * @return bool
	 */
	public function isLocked($key) {
		return self::locker()->isRecordLocked($this->function, $key);
	}

	/**
	 * Return LoginID of User who has locked function record
	 * @param  string $key       ID / Key of what is being locked in Function e.g. IWHM warehouse ID
	 * @return string
	 */
	public function getLockingUser($key) {
		return self::locker()->getLockingUser($this->function, $key);
	}

	/**
	 * Creates Function Lock
	 * @param  string $key        ID / Key of what is being locked in Function e.g. IWHM warehouse ID
	 * @return bool
	 */
	public function lock($key) {
		return self::locker()->lock($this->function, $key, $this->user->loginid);
	}

	/**
	 * Removes Lock(s)
	 * @param  string $key       ID / Key of what is being locked in Function e.g. IWHM warehouse ID
	 * @return bool
	 */
	public function deleteLock($key = false) {
		return self::locker()->deleteLock($this->user->loginid, $this->function, $key);
	}


}
