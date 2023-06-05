<?php namespace Dplus\Abstracts;
// Propel ORM Library
// use Propel\Runtime\Collection\ObjectCollection;
// use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Record;
// ProcessWire
use ProcessWire\Wire;
use ProcessWire\WireData;
use ProcessWire\WireException;
use ProcessWire\WireInput;
use ProcessWire\WireInputData;
// Dplus
use Dplus\CgiRequest;
use Dplus\Databases\Connectors\Dplus as DbDplus;
use Dplus\Misc\RecordLocker\UserFunction as FunctionLocker;

/**
 * AbstractModelCrudManager 
 * Base class for Record CRUD Operations
 */
abstract class AbstractModelCrudManager extends AbstractQueryWrapper {
	const MODEL              = '';
	const MODEL_KEY          = '';
	const MODEL_KEYS         = [];
	const MODEL_TABLE        = '';
	const DESCRIPTION        = '';
	const DESCRIPTION_RECORD   = '';
	const DESCRIPTION_RESPONSE = '';
	const RECORDLOCKER_FUNCTION = '';
	const SESSION_RESPONSE_KEY  = '';
	const YN_OPTIONS = ['Y' => 'Yes', 'N' => 'No'];
	const YN_TRUE    = 'Y';
	const YN_FALSE   = 'N';
	const FIELD_ATTRIBUTES = [];

	protected static $instance;

	protected $fieldAttributes = [];

	/** @return static */
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
		$this->initFieldAttributes();
	}

/* =============================================================
	Field Configs
============================================================= */
	/**
	 * Initialize Field Attributes
	 * NOTE: values may be set from configs
	 * @return void
	 */
	public function initFieldAttributes() {
		$this->fieldAttributes = static::FIELD_ATTRIBUTES;
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

/* =============================================================
	CRUD Processing
============================================================= */
	/**
	 * Process Data, Update Database
	 * @param  Wire $input Input Data
	 */
	public function process(Wire $data = null) {
		if (empty($data)) {
			$data = $this->input;
		}

		switch (get_class($data)) {
			case 'ProcessWire\\WireData';
				$input = $this->parseWireData($data);
				$this->processInput($input);
				break;
			case 'ProcessWire\\WireInput';
				$input = $this->parseWireInput($data);
				$this->processInput($input);
				break;
			case 'ProcessWire\\WireInputData';
				$this->processInput($data);
				break;
			default:
				throw new WireException("Invalid input object", 500);
				break;
		}
	}

	/**
	 * Return parsed WireData
	 * @param  WireData $data
	 * @return WireInputData
	 */
	protected function parseWireData(WireData $data) {
		$input = new WireInputData();
		$input->setArray($data->data);
		return $input;
	}
	
	/**
	 * Return parsed WireInput
	 * @param  WireData $data
	 * @return WireInputData
	 */
	protected function parseWireInput(WireInput $data) {
		$rm = strtolower($data->requestMethod());
		$input = $data->$rm;
		return $input;
	}

	/**
	 * Process Request
	 * @param  WireInputData $input
	 * @return void
	 */
	protected function processInput(WireInputData $input) {
		switch ($input->text('action')) {
			case 'delete':
				$this->inputDelete($input);
				break;
			case 'update':
				$this->inputUpdate($input);
				break;
		}
	}

	/**
	 * Update Record from Input Data
	 * @param  WireInputData $input Input Data
	 * @return bool
	 */
	protected function inputUpdate(WireInputData $input) {
		return $this->_inputUpdate($input);
	}

	/**
	 * Delete Record from Input Data
	 * @param  WireInputData $input Input Data
	 * @return bool
	 */
	protected function inputDelete(WireInputData $input) {
		return $this->_inputDelete($input);
	}

	/**
	 * Update Record(s)
	 * @param  WireInputData $input Input Data
	 * @return bool
	 */
	abstract protected function _inputUpdate(WireInputData $input);

	/**
	 * Delete Record(s)
	 * @param  WireInputData $input Input Data
	 * @return bool
	 */
	abstract protected function _inputDelete(WireInputData $input);

/* =============================================================
	CRUD Response
============================================================= */
	/**
	 * Return Response based on the outcome of the database save
	 * @param  Record   $r          Record
	 * @param  array    $invalidfields
	 * @return WireData
	 */
	abstract protected function saveAndRespond(Record $r, $invalidfields = []);

	/**
	 * Set Session Response
	 * @param AbstractResponse $response
	 */
	public function setResponse(AbstractResponse $response) {
		$this->wire('session')->setFor('response', static::SESSION_RESPONSE_KEY, $response->data);
	}

	/**
	 * Return Session Response
	 * @return AbstractResponse|false
	 */
	public function getResponse() {
		$r = $this->wire('session')->getFor('response', static::SESSION_RESPONSE_KEY);
		if (empty($r)) {
			return false;
		}
		$response = $this->newResponse();
		$response->setArray($r);
		return $response;
	}

	/**
	 * Delete Session Response
	 */
	public function deleteResponse() {
		$this->wire('session')->removeFor('response', static::SESSION_RESPONSE_KEY);
	}

	/**
	 * Return new Response
	 * @return AbstractResponse
	 */
	public function newResponse() {
		return new AbstractResponse();
	}

/* =============================================================
	Dplus Requests
============================================================= */
	/**
	 * Return Data needed for Dplus to UPDATE the Qnote
	 * @param  Record $r
	 * @return array
	 */
	public function writeRqstData(Record $r) {
		$dplusdb = DbDplus::instance()->dbconfig->dbName;
		return array_merge(["DBNAME=$dplusdb"], $this->generateRqstData($r));
	}

	/**
	 * Return array for Request
	 * @param  Record $r
	 * @return array
	 */
	abstract protected function generateRqstData(Record $r);

	/**
	 * Sends Update Request for Qnote
	 * @param  Record $r
	 * @return void
	 */
	public function updateDplus(Record $r) {
		$config = $this->wire('config');
		$data   = $this->writeRqstData($r);

		CgiRequest::instance()->writeFile($data, $this->sessionID);
		return CgiRequest::instance()->send($config->cgis['database'], $this->sessionID);
	}

/* =============================================================
	Record Locker Functions
============================================================= */
	/**
	 * Return Key for Record
	 * @param  Record   $r
	 * @return string
	 */
	public function recordlockerKey(Record $r) {
		$class = $this->modelClassName();
		$keys = [];
		foreach (static::MODEL_KEYS as $field) {
			$keys[] = $r->{$class::aliasproperty($field)};
		}
		return implode(FunctionLocker::glue(), $keys);
	}

	/**
	 * Lock Record
	 * @param  Record   $r Record
	 * @return bool
	 */
	public function lock(Record $r) {
		$key = $this->recordlockerKey($r);

		if ($this->recordlocker->isLocked($key) === false) {
			$this->recordlocker->lock($key);
		}
		return $this->recordlocker->userHasLocked($key);
	}
}