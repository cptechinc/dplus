<?php namespace Dplus\Codes;
// Purl URI Library
use Purl\Url;
// Propel Classes
use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
use Propel\Runtime\Collection\ObjectCollection;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as FunctionLocker;

/**
 * Base Class for Code Tables to implement
 */
abstract class Base extends WireData {
	const MODEL              = '';
	const MODEL_KEY          = '';
	const MODEL_TABLE        = '';
	const DESCRIPTION        = '';
	const DESCRIPTION_RECORD = '';
	const RESPONSE_TEMPLATE  = 'Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = '';
	const DPLUS_TABLE           = '';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => 4],
		'description' => ['type' => 'text', 'maxlength' => 20],
	];
	/** DPlus Permission Code */
	const PERMISSION = '';

	/** @var self */
	protected static $instance;

	/**
	 * Return Instance of class
	 * @return static
	 */
	public static function instance() {
		return static::getInstance();
	}

	/**
	 * Return Instance of class
	 * @return static
	 */
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
	 * Return the Code records from Database
	 * @return ObjectCollection
	 */
	public function codes() {
		$q = $this->query();
		return $q->find();
	}

	/**
	 * Return Array ready for JSON
	 * @param  Code  $code Code
	 * @return array
	 */
	public function codeJson(Code $code) {
		return ['code' => $code->code, 'description' => $code->description];
	}

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
