<?php namespace Dplus\Codes;
// Propel Classes
  // use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
  // use Propel\Runtime\Collection\ObjectCollection;
// ProcessWire
use ProcessWire\WireInput;
// Dplus Databases
use Dplus\Databases\Connectors\Dplus as DbDplus;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as FunctionLocker;

/**
 * AbstractCodeTableEditable
 * 
 * Handles Editing of Code Tables
 */
abstract class AbstractCodeTableEditable extends AbstractCodeTable {
	const MODEL              = '';
	const MODEL_KEY          = '';
	const MODEL_TABLE        = '';
	const DESCRIPTION        = '';
	const DESCRIPTION_RECORD = '';
	const RESPONSE_TEMPLATE  = 'Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = '';
	const DPLUS_TABLE           = '';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => 4, 'label' => 'Code'],
		'description' => ['type' => 'text', 'maxlength' => 20, 'label' => 'Description'],
	];
	const FILTERABLE_FIELDS = ['code', 'description'];

	/** DPlus Permission Code */
	const PERMISSION = '';

	protected static $instance;

	public static function instance() {
		return static::getInstance();
	}

	public static function getInstance() {
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
	 * Update Code from Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	abstract protected function inputUpdate(WireInput $input);

	/**
	 * Update Record with Input Data
	 * @param  WireInput $input Input Data
	 * @param  Code      $code
	 * @return array
	 */
	protected function _inputUpdate(WireInput $input, Code $code) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		if ($code->__isset('description')) { // Some Code tables may not use description
			$code->setDescription($values->text('description', ['maxLength' => $this->fieldAttribute('description', 'maxlength')]));
		}
		$code->setDate(date('Ymd'));
		$code->setTime(date('His'));
		$code->setDummy('P');
		return [];
	}


	/**
	 * Delete Code
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	abstract protected function inputDelete(WireInput $input);

/* =============================================================
	CRUD Response
============================================================= */
	/**
	 * Return Response based on the outcome of the database save
	 * @param  Code     $code          Code
	 * @param  array    $invalidfields
	 * @return Response
	 */
	protected function saveAndRespond(Code $code, $invalidfields = []) {
		$is_new = $code->isDeleted() ? false : $code->isNew();
		$saved  = $code->isDeleted() ? $code->isDeleted() : $code->save();
		
		$response = new Response();
		$response->setCode($code->id);
		$response->setKey($this->getRecordlockerKey($code));

		if ($saved) {
			$response->setSuccess(true);
		} else {
			$response->setError(true);
		}

		if ($is_new) {
			$response->setAction(Response::CRUD_CREATE);
		} elseif ($code->isDeleted()) {
			$response->setAction(Response::CRUD_DELETE);
		} else {
			$response->setAction(Response::CRUD_UPDATE);
		}

		$response->setFields($invalidfields);
		$this->addResponseMsgReplacements($code, $response);
		$response->buildMessage(static::RESPONSE_TEMPLATE);
		if ($response->hasSuccess()) {
			$this->updateDplus($code);
		}
		return $response;
	}

	/**
	 * Add Replacements, values for the Response Message
	 * @param Code     $code      Code
	 * @param Response $response  Response
	 */
	protected function addResponseMsgReplacements(Code $code, Response $response) {

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
	 * @param  Code $code  Code
	 * @return array
	 */
	protected function generateRequestData($code) {
		$dplusdb = DbDplus::instance()->dbconfig->dbName;
		$table   = static::DPLUS_TABLE;
		return ["DBNAME=$dplusdb", 'UPDATECODETABLE', "TABLE=$table", "CODE=$code->id"];
	}

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
	 * @param  Code   $code
	 * @return string
	 */
	public function getRecordlockerKey(Code $code) {
		return implode(FunctionLocker::glue(), [$code->id]);
	}

	/**
	 * Lock Code
	 * @param  Code   $code Code
	 * @return bool
	 */
	public function lockrecord(Code $code) {
		$key = $this->getRecordlockerKey($code);

		if ($this->recordlocker->isLocked($key) === false) {
			$this->recordlocker->lock($key);
		}
		return $this->recordlocker->userHasLocked($key);
	}
}
