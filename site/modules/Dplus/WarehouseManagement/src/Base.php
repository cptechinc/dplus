<?php namespace Dplus\Wm;

// ProcessWire Classes, Modules
use ProcessWire\WireData;

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

	protected function sendDplusRequest(array $data, $debug = false) {
		$db = $this->wire('modules')->get('DplusOnlineDatabase')->db_name;
		$data = array_merge(["DBNAME=$db"], $data);
		$requestor = $this->wire('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $this->sessionID);
		$requestor->cgi_request($this->wire('config')->cgis['warehouse'], $this->sessionID);
	}
}
