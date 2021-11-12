<?php namespace Dplus\Msa;
// Dplus Models
use DplusUserQuery, DplusUser;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as FunctionLocker;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus MSA Codes
use Dplus\Codes\Msa\Lgrp;

class Logm extends WireData {
	const MODEL              = 'DplusUser';
	const MODEL_KEY          = 'id';
	const TABLE              = 'syslogin';
	const DESCRIPTION        = 'Logm';
	const RESPONSE_TEMPLATE  = 'Logm User {id} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'logm';

	const FIELD_ATTRIBUTES = [
		'id'        => ['type' => 'text', 'maxlength' => DplusUser::LENGTH_USERID],
		'name'      => ['type' => 'text', 'maxlength' => 20],
		'companyid' => ['type' => 'text', 'maxlength' => 3],
		'whseid'    => ['type' => 'text', 'allowblank' => false],
		'printerbrowse'  => ['type' => 'text', 'allowblank' => false],
		'printerreport'  => ['type' => 'text', 'allowblank' => false],
		'groupid'        => ['type' => 'text', 'allowblank' => true],
		'roleid'         => ['type' => 'text', 'allowblank' => true],
	];

	public function __construct() {
		$this->sessionID = session_id();

		$this->recordlocker = new FunctionLocker();
		$this->recordlocker->setFunction(self::RECORDLOCKER_FUNCTION);
		$this->recordlocker->setUser($this->wire('user'));
	}

	private static $instance;

	public static function getInstance() {
		if (empty(self::$instance)) {
			$instance = new self();
			self::$instance = $instance;
		}
		return self::$instance;
	}

	/**
	 * Return JSON array for User
	 * @param  DplusUser $user
	 * @return array
	 */
	public function userJson(DplusUser $user) {
		return [
			'id'    => $user->id,
			'name'  => $user->name,
			'email' => $user->email
		];
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
	Query Functions
============================================================= */
	/**
	 * Return Query
	 * @return DplusUserQuery
	 */
	public function query() {
		return DplusUserQuery::create();
	}

	/**
	 * Return Query Filtered to User ID
	 * @param  string $id    User ID
	 * @return DplusUserQuery
	 */
	public function queryId($id) {
		$q = $this->query();
		$q->filterById($id);
		return $q;
	}

/* =============================================================
	Create, Read Functions
============================================================= */
	/**
	 * Return if User ID Exists
	 * @param  string $id    User ID
	 * @return bool
	 */
	public function exists($id) {
		$q = $this->queryId($id);
		return boolval($q->count());
	}

	/**
	 * Return User
	 * @param  string $id    User ID
	 * @return DplusUser
	 */
	public function user($id) {
		$q = $this->queryId($id);
		return $q->findOne();
	}

	/**
	 * Return new DplusUser
	 * @param  string $id    User ID
	 * @return DplusUser
	 */
	public function new($id) {
		$user = new DplusUser();
		$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('id', 'maxlength')]);
		if ($id != 'new') {
			$user->setId($id);
		}
		$user->setAdmin('N');
		$user->setStorefront('N');
		$user->setCitydesk('N');
		$user->setRestrictaccess('N');
		$user->setAllowprocessdelete('N');
		return $user;
	}

	/**
	 * Return New or Existing DplusUser
	 * @param  string $id    User ID
	 * @return DplusUser
	 */
	public function getOrCreate($id) {
		if ($this->exists($id) === false) {
			return $this->new($id);
		}
		return $this->user($id);
	}

/* =============================================================
	Input Functions
============================================================= */
	/**
	 * Process Input Data
	 * @param  WireInput $input Input Data
	 * @return void
	 */
	public function processInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		switch ($values->text('action')) {
			case 'update':
				$this->updateInput($input);
				break;
			case 'delete':
				$this->deleteInput($input);
				break;
		}
	}

	/**
	 * Update Logm User
	 * @param  WireInput $input Input Data
	 * @return void
	 */
	private function updateInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$user = self::getOrCreate($values->text('id'));

		if ($user->isNew() === false) {
			if ($this->lockrecord($user->id) === false) {
				$msg = "User ($user->id) is locked by " . $this->recordlocker->getLockingUser($user->id);
				$response = Response::responseError($msg);
				$response->setFunction(self::RECORDLOCKER_FUNCTION);
				$response->setKey($user->id);
				$this->setResponse($response);
				return false;
			}
		}
		return $this->updateInputUser($input, $user);
	}

	/**
	 * Update Logm User Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	private function updateInputUser(WireInput $input, DplusUser $user) {
		$rm = strtolower($input->requestMethod());
		$values  = $input->$rm;
		$invalid = [];

		$user->setName($values->text('name', ['maxLength' => $this->fieldAttribute('name', 'maxlength')]));
		$user->setCompanyid($values->text('companyid', ['maxLength' => $this->fieldAttribute('companyid', 'maxlength')]));
		$user->setAdmin($values->yn('admin'));
		$user->setStorefront($values->yn('storefront'));
		$user->setCitydesk($values->yn('citydesk'));
		$user->setReportadmin($values->yn('reportadmin'));
		$user->setUserwhsefirst($values->yn('userwhsefirst'));
		$user->setActiveitemsonly($values->yn('activteitemsonly'));
		$user->setRestrictaccess($values->yn('restrictaccess'));
		$user->setAllowprocessdelete($values->yn('allowprocessdelete'));
		$invalid = $this->updateInputUserValidated($input, $user);
		$user->setDate(date('Ymd'));
		$user->setTime(date('His'));

		$response = $this->saveAndRespond($user);
		$this->setResponse($response);
		return $response->hasSuccess();
	}

	/**
	 * Update User Fields that require validation, return invalid fields
	 * @param  WireInput $input  Input Data
	 * @param  DplusUser $user   User
	 * @return array
	 */
	private function updateInputUserValidated(WireInput $input, DplusUser $user) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$fields = ['whseid','printerbrowse','printerreport', 'groupid', 'roleid'];
		$invalid = [];

		$validateMin = new Validators\Min();
		if ($validateMin->whseid($values->text('whseid'))) {
			$invalid['whseid'] = 'Warehouse ID';
		}

		$prtd = Prtd::getInstance();
		if ($prtd->existsPrinterPitch($values->text('printerbrowse'))) {
			$invalid['printerbrowse'] = 'Default Printer';
		}
		if ($prtd->existsPrinterPitch($values->text('printerbrowse'))) {
			$invalid['printerreport'] = 'Report Printer';
		}

		$lgrp = Lgrp::getInstance();
		if ($values->text('groupid') != '' && $lgrp->exists($values->text('groupid')) === false) {
			$invalid['groupid'] = 'Login Group';
		}

		$lrole = Lrole::getInstance();
		if ($values->text('roleid') != '' && $lrole->exists($values->text('roleid')) === false) {
			$invalid['roleid'] = 'Login Role';
		}

		// Set valid field values
		foreach ($fields as $field) {
			if (array_key_exists($field, $invalid) === false) {
				$setFunc = 'set'.ucfirst($field);
				$user->$setFunc($values->text($field));
			}
		}
		return $invalid;
	}

/* =============================================================
	CRUD Response Functions
============================================================= */
	/**
	 * Returns Response based on the outcome of the database save
	 * @param  DplusUser $user          Record to record response of database save
	 * @param  array     $invalidfields Input fields that require attention
	 * @return Response
	 */
	private function saveAndRespond(DplusUser $user, array $invalidfields = []) {
		$is_new = $user->isDeleted() ? false : $user->isNew();
		$saved  = $user->isDeleted() ? $user->isDeleted() : $user->save();

		$response = new Response();
		$response->setFunction(self::RECORDLOCKER_FUNCTION);
		$response->setKey($user->id);

		if ($saved) {
			$response->setSuccess(true);
		} else {
			$response->setError(true);
		}

		if ($is_new) {
			$response->setAction(Response::CRUD_CREATE);
		} elseif ($user->isDeleted()) {
			$response->setAction(Response::CRUD_DELETE);
		} else {
			$response->setAction(Response::CRUD_UPDATE);
		}

		$response->addMsgReplacement('{id}', $user->id);
		$response->buildMessage(self::RESPONSE_TEMPLATE);



		if ($response->hasSuccess() && empty($invalidfields)) {
			// $this->requestUpdate($user->id);
		}
		$response->setFields($invalidfields);
		return $response;
	}

	/**
	 * Set Session Response
	 * @param Response $response
	 */
	protected function setResponse(Response $response) {
		$this->wire('session')->setFor('response', self::RECORDLOCKER_FUNCTION, $response);
	}

	/**
	 * Get Session Response
	 * @return Response|null
	 */
	public function getResponse() {
		return $this->wire('session')->getFor('response', self::RECORDLOCKER_FUNCTION);
	}

	/**
	 * Delete Response
	 * @return void
	 */
	public function deleteResponse() {
		return $this->wire('session')->removeFor('response', self::RECORDLOCKER_FUNCTION);
	}

	/**
	 * Return if Field has Error
	 * NOTE: Uses $session->response_itm->fields to derive this
	 * @param  string $inputname Input name e.g. commissiongroup
	 * @return bool
	 */
	public function fieldHasError($inputname) {
		$response = $this->getResponse();
		return ($response) ? array_key_exists($inputname, $response->fields) : false;
	}


/* =============================================================
	Supplemental Functions
============================================================= */
	/**
	 * Lock Record, validate User is locking Record
	 * @param  string $itemID ItemID
	 * @return bool
	 */
	public function lockrecord($id) {
		if ($this->recordlocker->isLocked($id) === false) {
			$this->recordlocker->lock($id);
		}
		return $this->recordlocker->userHasLocked($id);
	}

	/**
	 * Return Prtd
	 * @return Prtd
	 */
	public function getPrtd() {
		return Prtd::getInstance();
	}
}
