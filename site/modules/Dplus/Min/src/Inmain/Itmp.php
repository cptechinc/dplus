<?php namespace Dplus\Min;
// ProcessWire
use ProcessWire\WireData;
use ProcessWire\WireInput;
use ProcessWire\User;
// Dplus Model
use UserPermissionsItmQuery, UserPermissionsItm;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as FunctionLocker;
// Dplus Codes
use Dplus\Codes\Response;
// Dplus Msa
use Dplus\Msa\Logm;

class Itmp extends WireData {
	const MODEL              = 'UserPermissionsItm';
	const MODEL_KEY          = 'loginid';
	const MODEL_TABLE        = 'inv_itm_perm';
	const DESCRIPTION        = 'ITM Permissions';
	const DESCRIPTION_RECORD = 'ITM Permissions';
	const RESPONSE_TEMPLATE  = 'ITM Permissions {loginid} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'itmp';
	const USER_DEFAULT          = 'system';
	const PERMISSIONS_LABELS = [
		'whse'      => 'whse',
		'costing'   => 'costs',
		'pricing'   => 'prices',
		'xrefs'     => 'x-refs',
		'misc'      => 'misc',
		'options'   => 'options'
	];
	const PERMISSIONS_DEFAULT = [
		'whse'      => 'Y',
		'costing'   => 'Y',
		'pricing'   => 'Y',
		'xrefs'     => 'Y',
		'misc'      => 'Y',
		'options'   => 'Y',
	];

	private static $instance;

	/**
	 * Return Instance of Itmp
	 * @return Itmp
	 */
	public static function instance() {
		if (empty(self::$instance)) {
			$itmp = new Itmp();
			$itmp->init();
			self::$instance = $itmp;
		}
		return self::$instance;
	}

	/**
	 * Return Permissions Labels, and fields
	 * @return array
	 */
	public function permissionsLabels() {
		return self::PERMISSIONS_LABELS;
	}

	/**
	 * Return DEFAULT PERMISSIONS
	 * @return array
	 */
	public function defaultPermissions() {
		return self::PERMISSIONS_DEFAULT;
	}

	public function userJson(UserPermissionsItm $u) {
		$json = ['userid' => $u->userid, 'permissions' => []];

		foreach (self::PERMISSIONS_DEFAULT as $key => $label) {
			$json['permissions'][$key] = $u->$key;
		}
		return $json;
	}

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Query
	 * @return UserPermissionsItmQuery
	 */
	public function query() {
		return UserPermissionsItmQuery::create();
	}

	/**
	 * Return Query filtered By User ID
	 * @return UserPermissionsItmQuery
	 */
	public function queryUserid($userID) {
		$q = $this->query();
		$q->filterByUserid($userID);
		return $q;
	}

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return UserPermissionsItm[]
	 * @return UserPermissionsItm[]|ObjectCollection
	 */
	public function users() {
		$q = $this->query();
		return $q->find();
	}

	/**
	 * Return User IDs
	 * @return array
	 */
	public function userids() {
		$q = $this->query();
		$q->select(UserPermissionsItm::aliasproperty('userid'));
		return $q->find()->toArray();
	}

	/**
	 * Return if User Permissions Exists
	 * @param  string $userID Login ID
	 * @return bool
	 */
	public function exists($userID) {
		$q = $this->queryUserid($userID);
		return boolval($q->count());
	}

	/**
	 * Return UserPermissionsItm
	 * @param  string $userID Login ID
	 * @return UserPermissionsItm
	 */
	public function user($userID) {
		$q = $this->queryUserid($userID);
		return $q->findOne();
	}

	/**
	 * Return new UserPermissionsItm
	 * @param  string $userID
	 * @return UserPermissionsItm
	 */
	public function new($userID = '') {
		$user = new UserPermissionsItm();
		foreach (self::PERMISSIONS_DEFAULT as $key => $value) {
			$setFunction = "set".ucfirst($key);
			$user->$setFunction($value);
		}
		if (strlen($userID) && $userID != 'new') {
			$user->setUserid($userID);
		}
		return $user;
	}

	/**
	 * Return New or Existing User
	 * @param  string $userID
	 * @return UserPermissionsItm
	 */
	public function getOrCreate($userID) {
		if ($this->exists($userID)) {
			return $this->user($userID);
		}
		return $this->new($userID);
	}

	/**
	 * Return OptionsIi
	 * @param  string $userID Login ID
	 * @return OptionsIi
	 */
	public function userItmp($userID) {
		if ($this->exists($userID)) {
			return $this->user($userID);
		}

		if ($this->exists(self::USER_DEFAULT)) {
			return $this->user(self::USER_DEFAULT);
		}
		return $this->new($userID);
	}

	/**
	 * Return if User is allowed to II subfunction
	 * @param  User   $user
	 * @param  string $option II subfunction
	 * @return bool
	 */
	public function allowUser(User $user, $option = '') {
		$itmperm = $this->userItmp($user->loginid);
		$exists = array_key_exists($option, self::PERMISSIONS_DEFAULT);
		return $exists ? $itmperm->is_true($option) : true;
	}

	public function isUserAllowed(User $user, $option = '') {
		return $this->allowUser($user, $option);
	}

/* =============================================================
	CRUD Processing Functions
============================================================= */
	/**
	 * Takes Input, processses the action, calls the input_{$crud} to execute
	 * @param  WireInput $input Input
	 * @return void
	 */
	public function processInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		switch ($values->text('action')) {
			case 'update':
				$this->update($input);
			default:
				// TODO;
				break;
		}
	}

	/**
	 * Update Itmp Recordfrom Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function update(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$userID = $values->text('loginID');
		$invalidfields = [];
		$record = $this->getOrreate($userID);

		if (!$record->isNew()) {
			if (!$this->lockrecord($record->loginid)) {
				$message = self::DESCRIPTION_RECORD . " ($record->loginid)  was not saved, it is locked by " . $this->recordlocker->get_locked_user($record->loginid);
				$this->setResponse(Response::responseError($message));
				return false;
			}
		}
		$invalidfields = $this->updateItmpUser($input, $record);
		$response = $this->saveAndRespond($record, $invalidfields);
		$this->setResponse($response);
		return $response->hasSuccess();
	}

	/**
	 * Updates ITMP Record
	 * @param  WireInput        $input  Input Data
	 * @param  UserPermissionsItm $record ITMP Record
	 * @return array
	 */
	public function updateItmpUser(WireInput $input, UserPermissionsItm $record) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$invalidfields = [];
		$invalidfields = $this->updateItmpUserValidated($input, $record);

		foreach (self::PERMISSIONS_DEFAULT as $key => $default) {
			$setFunction = "set".ucfirst($key);
			$record->$setFunction($values->offsetExists($key) ? $values->yn($key) : $default);
		}

		$record->setDate(date('Ymd'));
		$record->setTime(date('His'));
		return $invalidfields;
	}

	/**
	 * Updates ITMP Record's Login ID
	 * Validates Each property is valid, returns invalid inputs, descriptions
	 * @param  WireInput          $input    Input Data
	 * @param  UserPermissionsItm $record   ITMP Record
	 * @return array
	 */
	protected function updateItmpUserValidated(WireInput $input, UserPermissionsItm $record) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$invalid = array();

		if (Logm::getInstance()->exists($values->text('loginid')) === false) {
			$invalid['loginid'] = 'Login ID';
		} else {
			$record->setLoginid($values->text('loginid'));
		}
		return $invalid;
	}

/* =============================================================
	CRUD Response
============================================================= */
	/**
	 * Return Response based on the outcome of the database save
	 * @param  UserPermissionsItm  $itmperm          Code
	 * @param  array               $invalidfields
	 * @return Response
	 */
	protected function saveAndRespond(UserPermissionsItm $itmperm, $invalidfields = []) {
		$is_new = $itmperm->isDeleted() ? false : $itmperm->isNew();
		$saved  = $itmperm->isDeleted() ? $itmperm->isDeleted() : $itmperm->save();

		$response = new Response();
		$response->setCode($itmperm->loginid);
		$response->setKey($itmperm->loginid);

		if ($saved) {
			$response->setSuccess(true);
		} else {
			$response->setError(true);
		}

		if ($is_new) {
			$response->setAction(Response::CRUD_CREATE);
		} elseif ($itmperm->isDeleted()) {
			$response->setAction(Response::CRUD_DELETE);
		} else {
			$response->setAction(Response::CRUD_UPDATE);
		}

		$response->setFields($invalidfields);
		$this->addResponseMsgReplacements($itmperm, $response);
		$response->buildMessage(static::RESPONSE_TEMPLATE);
		if ($response->hasSuccess()) {
			$this->updateDplus($itmperm);
		}
		return $response;
	}

	/**
	 * Add Replacements, values for the Response Message
	 * @param UserPermissionsItm     $itmperm      Code
	 * @param Response $response  Response
	 */
	protected function addResponseMsgReplacements(UserPermissionsItm $itmperm, Response $response) {
		$response->addMsgReplacement('{loginid}', $itmperm->loginid);
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
	Hook Functions
============================================================= */
	/**
	 * Set up Functions / Properties for pw_templated pages
	 * @return void
	 */
	public function init() {
		$this->recordlocker = new FunctionLocker();
		$this->recordlocker->setFunction(self::RECORDLOCKER_FUNCTION);
		$this->recordlocker->setUser($this->wire('user'));
	}

	/**
	 * Return Key for UserPermissionsItm
	 * @param  UserPermissionsItm $itmperm
	 * @return string
	 */
	public function getRecordlockerKey(UserPermissionsItm $itmperm) {
		return implode(FunctionLocker::glue(), [$itmperm->userid]);
	}

	/**
	 * Lock Record, validate User is locking Record
	 * @param  UserPermissionsItm $xref
	 * @return bool
	 */
	public function lockrecord($xref) {
		$key = $this->getRecordlockerKey($xref);
		if ($this->recordlocker->isLocked($key) === false) {
			$this->recordlocker->lock($key);
		}
		return $this->recordlocker->userHasLocked($key);
	}
}
