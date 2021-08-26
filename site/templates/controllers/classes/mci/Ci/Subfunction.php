<?php namespace Controllers\Mci\Ci;

abstract class Subfunction extends Base {
	const JSONCODE = '';

	private static $jsonm;

/* =============================================================
	Data Requests
============================================================= */
	protected static function sendRequest(array $data, $sessionID = '') {
		$sessionID = $sessionID ? $sessionID : session_id();
		$db = self::pw('modules')->get('DplusOnlineDatabase')->db_name;
		$data = array_merge(["DBNAME=$db"], $data);
		$requestor = self::pw('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $sessionID);
		$requestor->cgi_request(self::pw('config')->cgis['default'], $sessionID);
	}

/* =============================================================
	Displays
============================================================= */
	protected static function breadCrumbs() {
		return '';
	}

/* =============================================================
	Classes, Module Getters
============================================================= */
	public static function getJsonModule() {
		if (empty(self::$jsonm)) {
			self::$jsonm = self::pw('modules')->get('JsonDataFilesSession');
		}
		return self::$jsonm;
	}
}
