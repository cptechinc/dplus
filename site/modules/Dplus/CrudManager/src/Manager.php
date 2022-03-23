<?php namespace Dplus\Crud;
// Propel Classes
use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Record;
use Propel\Runtime\Collection\ObjectCollection;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as FunctionLocker;

abstract class Manager extends WireData {
	const MODEL              = '';
	const MODEL_KEY          = '';
	const MODEL_TABLE        = '';
	const DESCRIPTION        = '';
	const DESCRIPTION_RECORD = '';
	const RESPONSE_TEMPLATE  = 'Record {key} {not} {crud}';
	const RECORDLOCKER_FUNCTION = '';
	const DPLUS_TABLE           = '';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => 4],
		'description' => ['type' => 'text', 'maxlength' => 20],
	];
	/** DPlus Permission Code */
	const PERMISSION = '';

	protected static $instance;

	/**
	 * Return Instance
	 * @return static
	 */
	public static function instance() {
		if (empty(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	public function __construct() {
		$this->sessionID = session_id();
		$this->recordlocker = new FunctionLocker();
		$this->recordlocker->setFunction(static::RECORDLOCKER_FUNCTION);
		$this->recordlocker->setUser($this->wire('user'));
	}

/* =============================================================
	Field Configs
============================================================= */
	/**
	 * Return Field Attribute value
	 * @param  string $field Field Name
	 * @param  string $attr  Attribute Name
	 * @return mixed|bool
	 */
	public function fieldAttribute($field = '', $attr = '') {
		if (empty($field) || empty($attr)) {
			return false;
		}
		if (array_key_exists($field, static::FIELD_ATTRIBUTES) === false) {
			return false;
		}
		if (array_key_exists($attr, static::FIELD_ATTRIBUTES[$field]) === false) {
			return false;
		}
		return static::FIELD_ATTRIBUTES[$field][$attr];
	}

/* =============================================================
	Model Functions
============================================================= */
	/**
	 * Return Nodel Class Name
	 * @return string
	 */
	public function modelClassName() {
		return $this::MODEL;
	}

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Query Class Name
	 * @return string
	 */
	public function queryClassName() {
		return $this::MODEL.'Query';
	}

	/**
	 * Return New Query Class
	 * @return Query
	 */
	public function getQueryClass() {
		$class = static::queryClassName();
		return $class::create();
	}

	/**
	 * Returns the associated CodeQuery class for table code
	 * @return mixed
	 */
	public function query() {
		return $this->getQueryClass();
	}

/* =============================================================
	CRUD Reads
============================================================= */
	/**
	 * Return Array ready for JSON
	 * @param  Record  $record Code
	 * @return array
	 */
	abstract public function recordJson(Record $record);

/* =============================================================
	CRUD Creates
============================================================= */


/* =============================================================
	CRUD Processing
============================================================= */
	/**
	 * Process Input Data, Update Database
	 * @param  WireInput $input Input Data
	 */
	public function processInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		switch ($values->text('action')) {
			case 'delete':
			case 'delete-code':
				$this->inputDelete($input);
				break;
			case 'update-code':
			case 'update':
			case 'edit-code':
				$this->inputUpdate($input);
				break;
		}
	}

	/**
	 * Update Record from Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	abstract protected function inputUpdate(WireInput $input);

	/**
	 * Update Record with Input Data
	 * @param  WireInput $input Input Data
	 * @param  Record      $record
	 * @return array
	 */
	protected function _inputUpdate(WireInput $input, Record $record) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$record->setDate(date('Ymd'));
		$record->setTime(date('His'));
		$record->setDummy('P');
		return [];
	}


	/**
	 * Delete Record
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	abstract protected function inputDelete(WireInput $input);

/* =============================================================
	CRUD Response
============================================================= */
	/**
	 * Return Response based on the outcome of the database save
	 * @param  Record   $record          Record
	 * @param  array    $invalidfields
	 * @return Response
	 */
	protected function saveAndRespond(Record $record, $invalidfields = []) {
		$is_new = $record->isDeleted() ? false : $record->isNew();

		if ($record->isDeleted()) {
			$saved = true;
		}

		if ($record->isDeleted() === false) {
			$saved = empty($invalidfields) === false ? false : $record->save();
		}

		$response = new Response();
		$response->setKey($this->getRecordlockerKey($record));

		if ($saved) {
			$response->setSuccess(true);
		} else {
			$response->setError(true);
		}

		if ($is_new) {
			$response->setAction(Response::CRUD_CREATE);
		} elseif ($record->isDeleted()) {
			$response->setAction(Response::CRUD_DELETE);
		} else {
			$response->setAction(Response::CRUD_UPDATE);
		}

		$response->setFields($invalidfields);
		if (empty($invalidfields) === false) {
			$response->setRecordArray($record->toArray());
		}
		$this->addResponseMsgReplacements($record, $response);
		$response->buildMessage(static::RESPONSE_TEMPLATE);
		if ($response->hasSuccess()) {
			$this->updateDplus($record);
		}
		return $response;
	}

	/**
	 * Add Replacements, values for the Response Message
	 * @param Record   $record      Code
	 * @param Response $response  Response
	 */
	protected function addResponseMsgReplacements(Record $record, Response $response) {

	}

	/**
	 * Set Session Response
	 * @param Response $response
	 */
	public function setResponse(Response $response) {
		$this->wire('session')->setFor('response', static::RECORDLOCKER_FUNCTION, $response);
	}

	/**
	 * Return Session Response
	 * @return Response
	 */
	public function getResponse() {
		return $this->wire('session')->getFor('response', static::RECORDLOCKER_FUNCTION);
	}

	/**
	 * Delete Session Response
	 */
	public function deleteResponse() {
		$this->wire('session')->removeFor('response', static::RECORDLOCKER_FUNCTION);
	}

/* =============================================================
	Dplus Requests
============================================================= */
	/**
	 * Return Request Data Neeeded for Dplus Update
	 * @param  Record $record
	 * @return array
	 */
	abstract protected function generateRequestData(Record $record);

	/**
	 * Send Request do Dplus
	 * @param  array  $data  Request Data
	 * @return void
	 */
	protected function sendDplusRequest(array $data) {
		$config    = $this->wire('config');
		$requestor = $this->wire('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $this->sessionID);
		$requestor->cgi_request($config->cgis['database'], $this->sessionID);
	}

	/**
	 * Sends Dplus Cobol that Code Table has been Update
	 * @param  Code $code  Code
	 * @return void
	 */
	protected function updateDplus($code) {
		$data = $this->generateRequestData($code);
		$this->sendDplusRequest($data);
	}

/* =============================================================
	Permission Functions
============================================================= */
	/**
	 * Validate User Permission
	 * @return bool
	 */
	public function validateUserPermission() {
		if (empty(static::PERMISSION)) {
			return true;
		}
		$user = $this->user ? $this->user : $this->wire('user');
		return $user->hasPermissionCode(static::PERMISSION);
	}

/* =============================================================
	Record Locker Functions
============================================================= */
	/**
	 * Return Key for Code
	 * @param  Record   $record
	 * @return string
	 */
	public function getRecordlockerKey(Record $record) {
		return implode(FunctionLocker::glue(), []);
	}

	/**
	 * Lock Code
	 * @param  Record   $record Code
	 * @return bool
	 */
	public function lockrecord(Record $record) {
		$key = $this->getRecordlockerKey($record);

		if ($this->recordlocker->isLocked($key) === false) {
			$this->recordlocker->lock($key);
		}
		return $this->recordlocker->userHasLocked($key);
	}
}
