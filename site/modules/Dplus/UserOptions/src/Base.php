<?php namespace Dplus\UserOptions;
// Purl URI Library
use Purl\Url;
// Propel Classes
use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as UserRecord;
use Propel\Runtime\Collection\ObjectCollection;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as FunctionLocker;

abstract class Base extends WireData {
	const MODEL              = '';
	const MODEL_KEY          = '';
	const MODEL_TABLE        = '';
	const DESCRIPTION        = '';
	const DESCRIPTION_RECORD = '';
	const RESPONSE_TEMPLATE  = 'User {userid} {not} {crud}';
	const RECORDLOCKER_FUNCTION = '';
	const DPLUS_TABLE           = '';
	const USER_DEFAULT = 'system';
	const FIELD_ATTRIBUTES = [
		'userid'        => ['type' => 'text', 'maxlength' => 6],
	];

	const OPTIONS = [];

	protected static $instance;

	/**
	 * Return Instance
	 * @return self
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

	public function options() {
		return static::OPTIONS;
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
	 * Returns the associated UserRecordQuery class for table code
	 * @return mixed
	 */
	public function query() {
		return $this->getQueryClass();
	}

/* =============================================================
	CRUD Reads
============================================================= */
	/**
	 * Return the UserRecord records from Database
	 * @return ObjectCollection
	 */
	public function users() {
		$q = $this->query();
		return $q->find();
	}

	/**
	 * Return Query Filtered By User ID
	 * @param  string $userID User ID
	 * @return Query
	 */
	public function queryUserid($userID) {
		$q = $this->getQueryClass();
		$q->filterByUserid($userID);
		return $q;
	}

	/**
	 * Return if User Record Exists
	 * @param  string $userID User ID
	 * @return bool
	 */
	public function exists($userID) {
		$q = $this->queryUserid($userID);
		return boolval($q->count());
	}

	/**
	 * Return System User Record
	 * @return UserRecord
	 */
	public function default() {
		return $this->user(static::USER_DEFAULT);
	}

	/**
	 * Return User Record
	 * @param  string $userID User ID
	 * @return UserRecord
	 */
	public function user($userID) {
		$q = $this->queryUserid($userID);
		return $q->findOne();
	}

	/**
	 * Return User Record
	 * @param  string $userID User ID
	 * @return UserRecord
	 */
	public function userOrDefault($userID) {
		if ($this->exists($userID) === false) {
			return $this->default();
		}
		return $this->user($userID);
	}

	/**
	 * Return User Record
	 * @param  string $userID User ID
	 * @return UserRecord
	 */
	public function userOrNew($userID) {
		if ($this->exists($userID) === false) {
			return $this->new($userID);
		}
		return $this->user($userID);
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return new OptionsIi
	 * @param  string $userID
	 * @return UserRecord
	 */
	public function new($userID = '') {
		$class = $this->modelClassName();
		$r  = $class::new();

		if (strlen($userID) && $userID != 'new') {
			$r->setUserid($userID);
		}
		if ($userID != static::USER_DEFAULT) {
			$this->copyOptions(static::USER_DEFAULT, $r);
		}
		return $r;
	}

	/**
	 * Copies Options from User to another
	 * @param  string    $from User ID to copy from
	 * @param  UserRecord $to   Record to copy to
	 * @return UserRecord
	 */
	public function copyOptions($from = '', UserRecord $to) {
		if ($this->exists($from) == false) {
			return $to;
		}
		$template = $this->user($from);
		foreach(static::OPTIONS as $option) {
			$to->set($option, $template->$option);
		}
		return $to;
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
				$this->inputDelete($input);
				break;
			case 'update':
			case 'edit':
				$this->inputUpdate($input);
				break;
		}
	}

	/**
	 * Update UserRecord from Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	abstract protected function inputUpdate(WireInput $input);

	/**
	 * Update Record with Input Data
	 * @param  WireInput   $input   Input Data
	 * @param  UserRecord  $options
	 * @return array
	 */
	protected function _inputUpdate(WireInput $input, UserRecord $options) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		if ($options->__isset('description')) { // Some UserRecord tables may not use description
			$options->setDescription($values->text('description', ['maxLength' => $this->fieldAttribute('description', 'maxlength')]));
		}
		$options->setDate(date('Ymd'));
		$options->setTime(date('His'));
		$options->setDummy('P');
		return [];
	}


	/**
	 * Delete UserRecord
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	abstract protected function inputDelete(WireInput $input);

/* =============================================================
	CRUD Response
============================================================= */
	/**
	 * Return Response based on the outcome of the database save
	 * @param  UserRecord  $options          User Record
	 * @param  array       $invalidfields
	 * @return Response
	 */
	protected function saveAndRespond(UserRecord $options, $invalidfields = []) {
		$is_new = $options->isDeleted() ? false : $options->isNew();
		$saved  = $options->isDeleted() ? $options->isDeleted() : $options->save();

		$response = new Response();
		$response->setUserid($options->userid);
		$response->setKey($this->getRecordlockerKey($options));

		if ($saved) {
			$response->setSuccess(true);
		} else {
			$response->setError(true);
		}

		if ($is_new) {
			$response->setAction(Response::CRUD_CREATE);
		} elseif ($options->isDeleted()) {
			$response->setAction(Response::CRUD_DELETE);
		} else {
			$response->setAction(Response::CRUD_UPDATE);
		}

		$response->setFields($invalidfields);
		$this->addResponseMsgReplacements($options, $response);
		$response->buildMessage(static::RESPONSE_TEMPLATE);
		if ($response->hasSuccess()) {
			$this->updateDplus($options);
		}
		return $response;
	}

	/**
	 * Add Replacements, values for the Response Message
	 * @param UserRecord  $options   User Record
	 * @param Response    $response  Response
	 */
	protected function addResponseMsgReplacements(UserRecord $options, Response $response) {

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
	Record Locker Functions
============================================================= */
	/**
	 * Return Key for UserRecord
	 * @param  UserRecord   $options
	 * @return string
	 */
	public function getRecordlockerKey(UserRecord $options) {
		return implode(FunctionLocker::glue(), [$options->userid]);
	}

	/**
	 * Lock UserRecord
	 * @param  UserRecord   $options UserRecord
	 * @return bool
	 */
	public function lockrecord(UserRecord $options) {
		$key = $this->getRecordlockerKey($options);

		if ($this->recordlocker->isLocked($key) === false) {
			$this->recordlocker->lock($key);
		}
		return $this->recordlocker->userHasLocked($key);
	}
}
