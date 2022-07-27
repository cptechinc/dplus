<?php namespace Dplus\Wm;

// ProcessWire Classes, Modules
use ProcessWire\WireData;

use WhsesessionQuery, Whsesession;
// Dplus Databases
use Dplus\Databases\Connectors\Dpluso as DbDpluso;

class Base extends WireData {
	public function __construct() {
		$this->sessionID = session_id();
	}

	/**
	 * Sets Session ID
	 * @param string $sessionID
	 */
	public function setSessionID($sessionID = '') {
		$this->sessionID = $sessionID ? $sessionID : session_id();
	}

	/**
	 * Return Whsesession
	 * @return Whsesession
	 */
	public function getWhsesession() {
		return WhsesessionQuery::create()->findOneBySessionid($this->sessionID);
	}

	protected function sendDplusRequest(array $data, $debug = false) {
		$db = DbDpluso::instance()->dbconfig->dbName;
		$data = array_merge(["DBNAME=$db"], $data);
		$requestor = $this->wire('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $this->sessionID);
		$requestor->cgi_request($this->wire('config')->cgis['warehouse'], $this->sessionID);
	}
}
