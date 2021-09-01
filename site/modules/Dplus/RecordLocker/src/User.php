<?php namespace Dplus\RecordLocker;

use Propel\Runtime\ActiveQuery\Criteria;
use LockRecordQuery, LockRecord;

use ProcessWire\WireData, ProcessWire\User as PwUser;

/**
 * Locker
 *
 * Class for Creating, Reading LockRecord for the purposes of Dplus
 *
 * NOTE: Examples provided will be for IWHM
 */
class User extends WireData {
	private static $locker;
	private $function;
	private $user;

	public function __construct() {
		$this->user = $this->wire('user');
	}

	public function setUser(PwUser $user) {
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


	public static function glue() {
		$locker = self::locker();
		return $locker::GLUE;
	}

	/**
	 * Returns if Function is being locked by User ID
	 * @param  mixed  $key  ID / Key of what is being locked in Function e.g. IWHM warehouse ID
	 * @return bool
	 */
	public function userHasLocked($function, $key) {
		return self::locker()->userHasRecordLocked($function, $key, $this->user->loginid);
	}

	/**
	 * Remove All Locks for this user
	 * @return void
	 */
	public function deleteLocks() {
		$q = self::locker()->query();
		$q->filterByUserid($this->user->loginid);
		return $q->delete();
	}
}
