<?php namespace Dplus\UserOptions;
// Propel Classes
use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as UserRecord;
use Propel\Runtime\Collection\ObjectCollection;
// ProcessWire
use ProcessWire\WireData;
use ProcessWire\WireInput;
use ProcessWire\WireInputData;
use ProcessWire\User;
// Dplus
use Dplus\Codes\Min\Iwhm;
use Dplus\Databases\Connectors\Dplus as DbDplus;
use Dplus\Msa\Logm;
use Dplus\RecordLocker\UserFunction as FunctionLocker;

abstract class AbstractManager extends WireData {
	const NAME = '';
	const MODEL              = '';
	const MODEL_KEY          = '';
	const MODEL_TABLE        = '';
	const DESCRIPTION        = '';
	const DESCRIPTION_RECORD = '';
	const RESPONSE_TEMPLATE  = 'User {userid} was {not} {crud}';
	const RECORDLOCKER_FUNCTION = '';
	const DPLUS_TABLE           = '';
	const USER_DEFAULT = 'system';
	const WHSEID_ALL   = '**';
	const FIELD_ATTRIBUTES = [
		'userid'        => ['type' => 'text', 'maxlength' => 6, 'label' => 'User ID'],
	];
	const FILTERABLE_FIELDS = ['userid', 'name'];

	const SCREENS = [];

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

	public function screens() {
		return static::SCREENS;
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

	/**
	 * Return List of filterable fields
	 * @return array
	 */
	public function filterableFields() {
		return static::FILTERABLE_FIELDS;
	}

	/**
	 * Return Label for field
	 * @param  string $field
	 * @return string
	 */
	public function fieldLabel($field) {
		$label = $this->fieldAttribute($field, 'label');

		if ($label !== false) {
			return $label;
		}

		if ($field === 'name') {
			return 'Name';
		}

		if (in_array($field, ['userid'])) {
			return self::FIELD_ATTRIBUTES[$field]['label'];
		}
		return $field;
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
	 * Return list of all User IDs
	 * @return array
	 */
	public function userids() {
		$q = $this->query();
		$q->select($this->modelClassName()::aliasproperty('userid'));
		return $q->find()->toArray();
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

	/**
	 * Return if User is allowed to II subfunction
	 * @param  User   $user
	 * @param  string $option II subfunction
	 * @return bool
	 */
	public function allowUser(User $user, $option = '') {
		$userID = $this->exists($user->loginid) ? $user->loginid : static::USER_DEFAULT;
		$permissions = $this->user($userID);
		$exists = in_array($option, static::SCREENS);
		return $exists ? $permissions->isTrue($option) : true;
	}

	public function userJson(UserRecord $user) {
		$json = ['userid' => $user->userid];

		foreach (static::SCREENS as $screen) {
			$json[$screen] = $user->$screen;
		}
		return $json;
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
		$r = $class::new();
		$r->setDummy('P');

		if (strlen($userID) && $userID != 'new') {
			$r->setUserid($userID);
		}
		foreach (array_keys(static::FIELD_ATTRIBUTES) as $field) {
			if ($this->fieldAttribute($field, 'default') === false) {
				continue;
			}
			$r->set($field, $this->fieldAttribute($field, 'default'));
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
		foreach(static::SCREENS as $option) {
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
				$this->inputUpdate($input);
				break;
		}
	}

	/**
	 * Update User Record from Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function inputUpdate(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$user   = $this->userOrNew($values->string('userID'));

		if ($user->isNew() === false && $this->lockrecord($user) === false) {
			$message = "User ($user->userid) was not saved, it is locked by " . $this->recordlocker->getLockingUser($user->userid);
			$this->setResponse(Response::responseError($message));
			return false;
		}

		$this->_inputUpdate($user, $values);
		$user->setDate(date('Ymd'));
		$user->setTime(date('His'));
		$user->setDummy('P');

		$response = $this->saveAndRespond($user);
		$this->setResponse($response);
		return $response->hasSuccess();
	}

	/**
	 * Update Record fields
	 * @param  UserRecord    $user
	 * @param  WireInputData $values
	 * @return void
	 */
	protected function _inputUpdate(UserRecord $user, WireInputData $values) {
		$this->setUserScreensFromInputData($user, $values);
		$this->setUserScreensWhse($user, $values);
		$this->setUserScreensDetail($user, $values);
		$this->setUserScreensDate($user, $values);
	}

	/**
	 * Set User Screen Access
	 * @param  UserRecord    $user
	 * @param  WireInputData $values
	 * @return void
	 */
	protected function setUserScreensFromInputData(UserRecord $user, WireInputData $values) {
		foreach (static::SCREENS as $key) {
			$setScreen = "set" . ucfirst($key);
			$user->$setScreen($values->yn($key));
		}
	}

	/**
	 * Set User Screens' Whse ID
	 * @param  UserRecord    $user
	 * @param  WireInputData $values
	 * @return void
	 */
	protected function setUserScreensWhse(UserRecord $user, WireInputData $values) {
		$IWHM = Iwhm::instance();

		foreach (static::SCREENS as $screen) {
			if ($this->fieldAttribute($screen, 'whse') === false) {
				continue;
			}
			$field = 'whse'.$screen;
			$user->set($field, self::WHSEID_ALL);
			if (empty($values->text($field))) {
				continue;
			}
			if ($values->string($field) != self::WHSEID_ALL && $IWHM->exists($values->string($field)) === false) {
				continue;
			}
			$user->set($field, $values->string($field));
		}
	}

	/**
	 * Set User Screens' Detail
	 * @param  UserRecord    $user
	 * @param  WireInputData $values
	 * @return void
	 */
	protected function setUserScreensDetail(UserRecord $user, WireInputData $values) {
		foreach (static::SCREENS as $screen) {
			if ($this->fieldAttribute($screen, 'detail') === false) {
				continue;
			}
			$field = 'detail'.$screen;
			$user->set($field, $this->fieldAttribute($field, 'default'));
			
			if ($values->option($field, array_keys($this->fieldAttribute($field, 'options'))) === false) {
				continue;
			}
			$user->set($field, $values->option($field, array_keys($this->fieldAttribute($field, 'options'))));
		}
	}

	/**
	 * Set User Screens' Dates
	 * @param  UserRecord    $user
	 * @param  WireInputData $values
	 * @return void
	 */
	protected function setUserScreensDate(UserRecord $user, WireInputData $values) {
		foreach (static::SCREENS as $screen) {
			if ($this->fieldAttribute($screen, 'date') === false) {
				continue;
			}
			$fieldDays = 'days'.$screen;
			$fieldDate = 'date'.$screen;
			$optDays = ['max' => $this->fieldAttribute($fieldDays, 'max')];
			$user->set($fieldDays, $values->int($fieldDays, $optDays));

			$optDate = ['returnFormat' => $this->fieldAttribute($fieldDate, 'recordFormat'), 'default' => ''];
			$date = $values->date($fieldDate, $this->fieldAttribute($fieldDate, 'displayFormat'), $optDate);
			if (empty($date)) {
				$date = '';
			}
			echo $fieldDate . $date . '<br>';
			$user->set($fieldDate, $date);
		}
	}

	/**
	 * Delete User Record
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function inputDelete(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$userID = $values->string('userID');

		if ($this->exists($userID) === false) {
			return true;
		}

		$user = $this->user($userID);

		if ($this->lockrecord($user) === false) {
			$message = "User ($userID) was not deleted, it is locked by " . $this->recordlocker->getLockingUser($userID);
			$this->setResponse(Response::responseError($message));
			return false;
		}
		$user->delete();
		$response = $this->saveAndRespond($user);
		$this->setResponse($response);
		return $response->hasSuccess();
	}

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
		$response->setKey($options->userid);

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
	Dplus Requests
============================================================= */
	/**
	 * Sends Dplus Cobol that User Options
	 * @param  UserRecord $user
	 * @return void
	 */
	protected function updateDplus(UserRecord $user) {
		$config  = $this->wire('config');
		$dplusdb = DbDplus::instance()->dbconfig->dbName;
		$table = static::DPLUS_TABLE;
		$data = ["DBNAME=$dplusdb", 'UPDATECODETABLE', "TABLE=$table", "CODE=$user->userid"];
		$requestor = $this->wire('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $this->sessionID);
		$requestor->cgi_request($config->cgis['database'], $this->sessionID);
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

/* =============================================================
	Supplemental Functions
============================================================= */
	/**
	 * Return User's name
	 * @param  string $userID  User ID
	 * @return string
	 */
	public function logmUserName($userID) {
		return Logm::getInstance()->name($userID);
	}
}
