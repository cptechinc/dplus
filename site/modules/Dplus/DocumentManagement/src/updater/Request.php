<?php namespace Dplus\DocManagement\Updater;
// ProcessWire
use ProcessWire\WireData;

/**
 * Request
 * Sends Document Update Requests to Dplus
 * @property string $sessionID   Session ID
 * @property string $folder      DocumentManagement Folder Code
 * @property string $field1      Document Field 1 Value
 * @property string $field2      Document Field 2 Value
 * @property string $field3      Document Field 3 Value
 * @property string $file        File Path
 */
class Request extends WireData {
	public function __construct() {
		$this->sessionID = session_id();
		$this->folder = '';
		$this->field1 = '';
		$this->field2 = '';
		$this->field3 = '';
		$this->file   = '';
	}

	public function request() {
		$this->updateDplus();
	}

	/**
	 * Send Request to Dplus
	 * @return void
	 */
	private function updateDplus() {
		$config    = $this->wire('config');
		$requestor = $this->wire('modules')->get('DplusRequest');
		$requestor->write_dplusfile($this->requestData(), $this->sessionID);
		$requestor->cgi_request($config->cgis['database'], $this->sessionID);
	}

	/**
	 * Return Request Data
	 * @return array
	 */
	private function requestData() {
		$dplusdb = $this->wire('modules')->get('DplusOnlineDatabase')->db_name;
		return [
			"DBNAME=$dplusdb",
			"DOCFILEFIELDS=$this->folder",
			"DOCFLD1=$this->field1",
			"DOCFLD2=$this->field2",
			"DOCFLD3=$this->field3",
			"DOCFILENAME=$this->file",
		];
	}
}
