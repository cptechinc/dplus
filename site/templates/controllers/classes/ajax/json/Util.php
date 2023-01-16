<?php namespace Controllers\Ajax\Json;
// ProcessWire
use ProcessWire\WireData;
// Dplus
use Dplus\RecordLocker\UserFunction as Locker;


class Util extends AbstractJsonController {
	public static function recordLockerUserCanLock(WireData $data) {
		$fields = ['function|text', 'key|string'];
		self::sanitizeParametersShort($data, $fields);
		$locker = new Locker();
		$locker->setFunction($data->function);

		if ($locker->isLocked($data->key) === false) {
			return true;
		}
		return $locker->isLockedByUser($data->key);
	}

	public static function recordLockerLock(WireData $data) {
		$fields = ['function|text', 'key|string'];
		self::sanitizeParametersShort($data, $fields);
		$locker = new Locker();
		$locker->setFunction($data->function);
		if ($locker->isLocked($data->key) && $locker->isLockedByUser($data->key) === false) {
			return false;
		}
		return $locker->lock($data->key);
	}

	public static function recordLockerDelete(WireData $data) {
		$fields = ['function|text', 'key|string'];
		self::sanitizeParametersShort($data, $fields);
		$locker = new Locker();
		$locker->setFunction($data->function);
		if ($locker->isLocked($data->key) === false) {
			return true;
		}
		if ($data->key) {
			return $locker->deleteLock($data->key);
		}
		return $locker->deleteLock();
	}

	

}
