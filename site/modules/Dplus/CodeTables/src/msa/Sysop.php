<?php namespace Dplus\Codes\Msa;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// Dplus Models
use MsaSysopCodeQuery, MsaSysopCode;
// ProcessWire
use ProcessWire\WireInput;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as FunctionLocker;
// Dplus Codes
use Dplus\Codes\Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the Sysop code table
 */
class Sysop extends Base {
	const MODEL              = 'MsaSysopCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'sys_opt_options';
	const DESCRIPTION        = 'System Optional Code';
	const DESCRIPTION_RECORD = 'System Optional Code';
	const RESPONSE_TEMPLATE  = 'System {system} Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'sysop';
	const DPLUS_TABLE           = 'SYSOP';
	const FIELD_ATTRIBUTES = [
		'system'      => [
			'options' => [
				'AP' => 'Accounts Payable/Vendor',
				'AR' => 'Accounts Receivable/Customer',
				'IN' => 'Inventory/Item',
				'MS' => 'Miscellaneous',
				'PO' => 'Purchase Order',
				'SO' => 'Sales Order',
			]
		],
		'code'        => ['type' => 'text', 'maxlength' => MsaSysopCode::MAX_LENGTH_CODE],
		'description' => ['type' => 'text', 'maxlength' => 20],
		'notecode'    => ['type' => 'text', 'maxlength' => 4],
		'sequence'    => ['type' => 'number', 'max' => 999],
		'fieldtype'   => ['type' => 'text', 'options' => ['C' => 'Character', 'N' => 'Numeric'], 'default' => 'C'],
		'beforedecimal' => ['type' => 'number', 'max' => 9, 'default' => 0],
		'afterdecimal' => ['type' => 'number', 'max' => 9, 'default' => 0],
		'advsearch'    => ['type' => 'text',  'default' => 'N'],
		'validate'     => ['type' => 'text',  'default' => 'Y'],
		'force'        => ['type' => 'text',  'default' => 'N'],
		'webvalidate'  => ['type' => 'text',  'default' => 'N'],
		'webforce'     => ['type' => 'text',  'default' => 'N'],
		'filename'     => ['type' => 'text',  'default' => 'N'],
	];

	protected static $instance;

/* =============================================================
	Json
============================================================= */
	/**
	 * Return JSON
	 * @param  Code $opt
	 * @return array
	 */
	public function codeJson(Code $opt) {
		return [
			'system'      => $opt->system,
			'sysop'       => $opt->id,
			'id'          => $opt->id,
			'description' => $opt->description,
			'input' => [
				'validate' => $opt->validate(),
				'force'    => $opt->force(),
				'filename' => $opt->isFilename(),
				'notetype' => $opt->notecode
			]
		];
	}

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Query Filtered for Sysop Code
	 * @param  string $system  System Code
	 * @param  string $id      Optional Code
	 * @return MsaSysopCodeQuery
	 */
	public function queryCode($system, $id) {
		$q = $this->query();
		$q->filterBySystem($system);
		$q->filterById($id);
		return $q;
	}

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * REturn if System Exists
	 * @param  string $system  System Code
	 * @return bool
	 */
	public function systemExists($system) {
		return array_key_exists($system, $this->fieldAttribute('system', 'options'));
	}

	/**
	 * Return the Code records from Database
	 * @return ObjectCollection
	 */
	public function codes() {
		$q = $this->query();
		return $q->find();
	}

	/**
	 * Return if Sysop Code Exists
	 * @param  string $system  System Code
	 * @param  string $id      Optional Code
	 * @return bool
	 */
	public function exists($system, $id) {
		$q = $this->queryCode($system, $id);
		return boolval($q->count());
	}

	/**
	 * Return Sysop Code
	 * @param  string $system  System Code
	 * @param  string $id      Optional Code
	 * @return MsaSysopCode
	 */
	public function code($system, $id) {
		$q = $this->queryCode($system, $id);
		return $q->findOne();
	}

	/**
	 * Return Option Code is a Note
	 * @param  string $system  System
	 * @param  string $id      Option Code
	 * @return bool
	 */
	public function isNote($system, $id) {
		$q = $this->queryCode($system, $id);
		$q->select(MsaSysopCode::aliasproperty('notecode'));
		return boolval($q->findOne());
	}

	/**
	 * Return Option Code is Required
	 * @param  string $system  System
	 * @param  string $id      Option Code
	 * @return bool
	 */
	public function isRequired($system, $id) {
		$q = $this->queryCode($system, $id);
		$q->select(MsaSysopCode::aliasproperty('force'));
		return $q->findOne() == MsaSysopCode::YN_TRUE;
	}

	/**
	 * Return Option Code Note Code
	 * @param  string $system  System
	 * @param  string $id      Option Code
	 * @return bool
	 */
	public function notecode($system, $id) {
		$q = $this->queryCode($system, $id);
		$q->select(MsaSysopCode::aliasproperty('notecode'));
		return $q->findOne();
	}

	/**
	 * Return if Note Code Exists
	 * @param  string $notecode Note Code
	 * @return bool
	 */
	public function notecodeExists($notecode) {
		$q = $this->query();
		$q->filterByNotecode($notecode);
		return boolval($q->count());
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @param  string $system  System Code
	 * @param  string $id      Optional Code
	 * @return MsaSysopCode
	 */
	public function new($system = '', $id = '') {
		$code = new MsaSysopCode();
		$systems = $this->fieldAttribute('system', 'options');

		if (array_key_exists($system, $systems)) {
			$code->setSystem($system);
		}

		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}

		$code->setFilename($this->fieldAttribute('filename', 'default'));
		$code->setValidate($this->fieldAttribute('validate', 'default'));
		$code->setForce($this->fieldAttribute('force', 'default'));
		$code->setWebvalidate($this->fieldAttribute('webvalidate', 'default'));
		$code->setWebforce($this->fieldAttribute('webforce', 'default'));
		$code->setFieldtype($this->fieldAttribute('fieldtype', 'default'));
		$code->setAfterdecimal($this->fieldAttribute('afterdecimal', 'default'));
		$code->setBeforedecimal($this->fieldAttribute('beforedecimal', 'default'));
		$code->setAdvsearch($this->fieldAttribute('advsearch', 'default'));
		return $code;
	}

	/**
	 * Return New or Existing Code
	 * @param  string $system  System Code
	 * @param  string $id      Optional Code
	 * @return Code
	 */
	public function getOrCreate($system = '', $id = '') {
		if ($this->exists($system, $id)) {
			return $this->code($system, $id);
		}
		return $this->new($system, $id);
	}

/* =============================================================
	CRUD Processing
============================================================= */
	/**
	 * Update Code from Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function inputUpdate(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$invalidfields = [];
		$system = $values->text('system');

		if ($this->systemExists($system) === false) {
			$this->setResponse(Response::responseError("System $system not found"));
			return false;
		}
		$code = $this->getOrCreate($values->text('system'), $values->text('code'));
		$invalidfields = $this->_inputUpdate($input, $code);
		$response = $this->saveAndRespond($code, $invalidfields);
		$this->setResponse($response);
		return $response->hasSuccess();
	}

	/**
	 * Update Record with Input Data
	 * @param  WireInput $input Input Data
	 * @param  Code      $code
	 * @return array
	 */
	protected function _inputUpdate(WireInput $input, Code $code) {
		parent::_inputUpdate($input, $code);
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$invalidfields = [];

		$code->setFilename($values->yn('filename'));
		$code->setValidate($values->yn('validate'));
		$code->setForce($values->yn('force'));
		$code->setAdvsearch($values->yn('advsearch'));
		$code->setWebvalidate($values->yn('webvalidate'));
		$code->setWebforce($values->yn('webforce'));
		$code->setDocfolder($values->yn('docfolder'));
		$code->setSequence($values->int('sequence', ['max' => $this->fieldAttribute('sequence', 'max')]));
		$code->setFieldtype($values->yn('fieldtype'));

		if ($code->isNumeric()) {
			$code->setBeforedecimal($values->int('beforedecimal', ['max' => $this->fieldAttribute('beforedecimal', 'max')]));
			$code->setAfterdecimal($values->int('afterdecimal', ['max' => $this->fieldAttribute('afterdecimal', 'max')]));
		}
		$notecode = strtolower($values->text('notecode', ['maxLength' => $this->fieldAttribute('notecode', 'maxlength')]));

		if ($code->notecode != $notecode && $this->notecodeExists($notecode)) {
			$invalidfields['notecode'] = "Note Code $notecode exists";
		} else {
			$code->setNotecode($notecode);
		}
		return $invalidfields;
	}


	/**
	 * Delete Code
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function inputDelete(WireInput $input) {

	}

/* =============================================================
	CRUD Response
============================================================= */
	/**
	 * Add Replacements, values for the Response Message
	 * @param Code     $code      Code
	 * @param Response $response  Response
	 */
	protected function addResponseMsgReplacements(Code $code, Response $response) {
		$response->addMsgReplacement("{system}", $code->system);
	}

/* =============================================================
	Dplus Requests
============================================================= */
	/**
	 * Sends Dplus Cobol that Code Table has been Update
	 * @param  Code $code  Code
	 * @return void
	 */
	protected function updateDplus($code) {
		$config  = $this->wire('config');
		$dplusdb = $this->wire('modules')->get('DplusDatabase')->db_name;
		$table = static::DPLUS_TABLE;
		$data = ["DBNAME=$dplusdb", 'UPDATESYSOP', "SYS=$code->system", "CODE=$code->id"];
		$requestor = $this->wire('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $this->sessionID);
		$requestor->cgi_request($config->cgis['database'], $this->sessionID);
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
		return implode(FunctionLocker::glue(), [$code->system, $code->code]);
	}

/* =============================================================
	Supplemental
============================================================= */
	/**
	 * Return Codes That Are Required
	 * @param  string $system
	 * @return array
	 */
	public function getRequiredCodes($system = '') {
		$q = $this->query();
		if ($system) {
			$q->filterBySystem($system);
		}
		$q->select(MsaSysopCode::aliasproperty('code'));
		$q->filterByForce(MsaSysopCode::YN_TRUE);
		return $q->find()->toArray();
	}
}
