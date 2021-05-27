<?php namespace Dplus\RecordLocker;

use Propel\Runtime\ActiveQuery\Criteria;
use LockRecordQuery, LockRecord;

use ProcessWire\WireData;

/**
 * Locker
 *
 * Class for Creating, Reading LockRecord for the purposes of Dplus
 *
 * NOTE: Examples provided will be for IWHM
 */
class Locker extends WireData {
	/**
	 * Return LockRecordQuery
	 * @return LockRecordQuery
	 */
	public function query() {
		return LockRecordQuery::create();
	}

	/**
	 * Returns if Function is being locked by User ID
	 * @param  string $function  Function e.g IWHM
	 * @param  mixed $key       ID / Key of what is being locked in Function e.g. IWHM warehouse ID
	 * @param  string $userID    User Login ID
	 * @return bool
	 */
	public function userHasRecordLocked($function, $key, $userID) {
		$key = is_array($key) ? implode('-', $key) : $key;
		$q = $this->query();
		$q->filterByUserid($userID);
		$q->filterByFunction($function);
		$q->filterByKey($key);
		return boolval($q->count());
	}

	/**
	 * Returns if Function is being locked
	 * @param  string $function  Function e.g IWHM
	 * @param  string $key       ID / Key of what is being locked in Function e.g. IWHM warehouse ID
	 * @return bool
	 */
	public function isRecordLocked($function, $key) {
		$key = is_array($key) ? implode('-', $key) : $key;
		$q = $this->query();
		$q->filterByFunction($function);
		$q->filterByKey($key);
		return boolval($q->count());
	}

	/**
	 * Return LoginID of User who has locked function record
	 * @return string
	 */
	public function getLockingUser($function, $key) {
		$key = is_array($key) ? implode('-', $key) : $key;
		$q = $this->query();
		$q->select('userid');
		$q->filterByFunction($function);
		$q->filterByKey($key);
		return $q->findOne();
	}

	/**
	 * Creates Function Lock
	 * @param  string $function   Function e.g IWHM
	 * @param  string $key        ID / Key of what is being locked in Function e.g. IWHM warehouse ID
	 * @param  string $function   Function e.g IWHM
	 * @param  string $userID     User Login ID
	 * @return bool
	 */
	public function lock($function, $key, $userID) {
		$key = is_array($key) ? implode('-', $key) : $key;
		$lock = new LockRecord();
		$lock->setFunction($function);
		$lock->setKey($key);
		$lock->setUserid($userID);
		$lock->setDate(date(LockRecord::DATE_FORMAT));
		return $lock->save();
	}

	/**
	 * Removes Lock(s)
	 * @param  string $userID    User Login ID
	 * @param  string $function  Function e.g IWHM
	 * @param  string $key       ID / Key of what is being locked in Function e.g. IWHM warehouse ID
	 * @return bool
	 */
	public function deleteLock($userID, $function, $key = false) {
		$key = is_array($key) ? implode('-', $key) : $key;
		$q = $this->query();
		$q->filterByUserid($userID);
		$q->filterByFunction($function);

		if ($key) {
			$q->filterByKey($key);
		}
		return $q->delete();
	}

	/**
	 * Deletes Locks for User older than $hours hours
	 * @param  string $userID User ID
	 * @param  int    $hours  Number of Hours to go back
	 * @return bool
	 */
	public function deleteLocksOlderThan($userID = 'all', int $hours) {
		$datetime = date(LockRecord::DATE_FORMAT, strtotime("-$hours hours"));
		$q = $this->query();

		if ($userID != 'all') {
			$q->filterByUserid($userID);
		}
		$q->filterByDate($datetime, Criteria::LESS_THAN);
		return $q->delete();
	}
}
