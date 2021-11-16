<?php namespace Dplus\DocManagement;
// ProcessWire
use ProcessWire\WireData;
// Dplus Document Management
use Dplus\DocManagement\Updater\Request;
/**
 * Updater
 * Holds Document Data to send File Update Request
 *
 * @property string $sessionID    Session ID
 * @property string $folder       DocumentManagement Folder Code
 * @property string $field1       Document Field 1 Value
 * @property string $field2       Document Field 2 Value
 * @property string $field3       Document Field 3 Value
 * @property string $directory    File Directory
 * @property string $filelocation File Location
 */
class Updater extends WireData {
	const TAG = '';
	const FOLDER = '';

	public function __construct() {
		$this->sessionID = session_id();
		$this->field1 = '';
		$this->field2 = '';
		$this->field3 = '';
		$this->directory  = '';
		$this->filelocation = '';
	}

	/**
	 * Map Field Properties from Other Properties
	 * @return void
	 */
	public function mapFields() {

	}

	/**
	 * Send Request
	 * @return bool
	 */
	public function update() {
		$this->mapFields();

		$rqst = new Request();
		$rqst->folder    = static::FOLDER;
		$rqst->field1    = $this->field1;
		$rqst->field2    = $this->field2;
		$rqst->field3    = $this->field3;
		$rqst->directory = $this->directory;
		$rqst->filelocation = $this->filelocation;
		$rqst->request();
		return true;
	}

}
