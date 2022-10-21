<?php namespace Dplus\Xrefs;
// Propel Classes
use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Xref;
// use Propel\Runtime\Collection\ObjectCollection;
// ProcessWire
use ProcessWire\WireData;
use ProcessWire\WireInput;
// Dplus Databases
use Dplus\Databases\Connectors\Dplus as DbDplus;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as FunctionLocker;

/**
 * AbstractXrefManager
 * Class for handling CRUD of Xref Records
 */
abstract class AbstractXrefManager extends WireData {
	const MODEL              = '';
	const MODEL_KEY          = [];
	const MODEL_TABLE        = '';
	const DESCRIPTION        = '';
	const DESCRIPTION_RECORD = '';
	const RECORDLOCKER_FUNCTION = '';
	const FIELD_ATTRIBUTES = [
		// 'code'        => ['type' => 'text', 'maxlength' => 4, 'label' => 'Xref'],
		// 'description' => ['type' => 'text', 'maxlength' => 20, 'label' => 'Description'],
	];
	const FILTERABLE_FIELDS = ['code', 'description'];
	const RESPONSE_TEMPLATE  = 'Xref {code} {not} {crud}';
	/** DPlus Permission Xref */
	const PERMISSION = '';

	protected static $instance;

	public static function instance() {
		if (empty(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * Return Array ready for JSON
	 * @param  Xref  $xref Xref
	 * @return array
	 */
	abstract public function xrefJson(Xref $xref);

/* =============================================================
	Field Configs
============================================================= */
	public function initFieldAttributes() {
		$attributes = static::FIELD_ATTRIBUTES;
		$this->fieldAttributes = $attributes;
	}

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
		if (empty($this->fieldAttributes)) {
			$this->initFieldAttributes();
		}

		if (array_key_exists($field, $this->fieldAttributes) === false) {
			return false;
		}
		if (array_key_exists($attr, $this->fieldAttributes[$field]) === false) {
			return false;
		}
		return $this->fieldAttributes[$field][$attr];
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
		return $field;
	}

	public function __construct() {
		$this->sessionID = session_id();
		$this->recordlocker = new FunctionLocker();
		$this->recordlocker->setFunction(static::RECORDLOCKER_FUNCTION);
		$this->recordlocker->setUser($this->wire('user'));
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
	 * Returns the associated XrefQuery class for table code
	 * @return mixed
	 */
	public function query() {
		return $this->getQueryClass();
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
	 * Update Xref from Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	abstract protected function inputUpdate(WireInput $input);

	/**
	 * Update Record with Input Data
	 * @param  WireInput $input Input Data
	 * @param  Xref      $xref
	 * @return array
	 */
	protected function _inputUpdate(WireInput $input, Xref $xref) {
		$xref->setDate(date('Ymd'));
		$xref->setTime(date('His'));
		$xref->setDummy('P');
		return [];
	}


	/**
	 * Delete Xref
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	abstract protected function inputDelete(WireInput $input);


/* =============================================================
	CRUD Response
============================================================= */
	/**
	 * Return Response based on the outcome of the database save
	 * @param  Xref     $xref          Xref
	 * @param  array    $invalidfields
	 * @return Response
	 */
	protected function saveAndRespond(Xref $xref, $invalidfields = []) {
		$is_new = $xref->isDeleted() ? false : $xref->isNew();
		$saved  = $xref->isDeleted() ? $xref->isDeleted() : $xref->save();

		$response = new Response();
		$response->setKey($this->getRecordlockerKey($xref));

		if ($saved) {
			$response->setSuccess(true);
		} else {
			$response->setError(true);
		}

		if ($is_new) {
			$response->setAction(Response::CRUD_CREATE);
		} elseif ($xref->isDeleted()) {
			$response->setAction(Response::CRUD_DELETE);
		} else {
			$response->setAction(Response::CRUD_UPDATE);
		}

		$response->setFields($invalidfields);
		$this->addResponseMsgReplacements($xref, $response);
		$response->buildMessage(static::RESPONSE_TEMPLATE);
		if ($response->hasSuccess()) {
			$this->updateDplus($xref);
		}
		return $response;
	}

	/**
	 * Add Replacements, values for the Response Message
	 * @param Xref     $xref      Xref
	 * @param Response $response  Response
	 */
	protected function addResponseMsgReplacements(Xref $xref, Response $response) {

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
	Dplus Requests
============================================================= */
	/**
	 * Return Request Data Neeeded for Dplus Update
	 * @param  Xref $xref  Xref
	 * @return array
	 */
	protected function generateRequestData(Xref $xref) {
		$dplusdb = DbDplus::instance()->dbconfig->dbName;
		return array_merge(["DBNAME=$dplusdb"], $this->_generateRequestData($xref));
	}

	abstract protected function _generateRequestData(Xref $xref);

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
	 * Sends Dplus Cobol that Xref Table has been Update
	 * @param  Xref $xref  Xref
	 * @return void
	 */
	protected function updateDplus(Xref $xref) {
		$data = $this->generateRequestData($xref);
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
		return $user->hasPermissionXref(static::PERMISSION);
	}

/* =============================================================
	Record Locker Functions
============================================================= */
	/**
	 * Return Key for Xref
	 * @param  Xref   $xref
	 * @return string
	 */
	public function getRecordlockerKey(Xref $xref) {
		$class = $this->modelClassName();
		$keys = [];
		foreach (static::MODEL_KEY as $field) {
			$keys[] = $xref->{$class::aliasproperty($field)};
		}
		return implode(FunctionLocker::glue(), $keys);
	}

	/**
	 * Lock Xref
	 * @param  Xref   $xref Xref
	 * @return bool
	 */
	public function lockrecord(Xref $xref) {
		$key = $this->getRecordlockerKey($xref);

		if ($this->recordlocker->isLocked($key) === false) {
			$this->recordlocker->lock($key);
		}
		return $this->recordlocker->userHasLocked($key);
	}

}
