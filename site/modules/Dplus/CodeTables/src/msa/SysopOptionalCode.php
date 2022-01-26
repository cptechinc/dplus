<?php namespace Dplus\Codes\Msa;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// Dplus Models
use SysopOptionalCodeQuery, SysopOptionalCode as Model;
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
abstract class SysopOptionalCode extends Base {
	const MODEL              = 'SysopOptionalCode';
	const MODEL_KEY          = 'system,sysop,id';
	const MODEL_TABLE        = 'sys_opt_optcode';
	const DESCRIPTION        = 'Sysop Optional Code';
	const DESCRIPTION_RECORD = 'Sysop Optional Code';
	const RESPONSE_TEMPLATE  = 'Sysop {sysop} Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = '';
	const DPLUS_TABLE           = '';
	const SYSTEM = '';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => Model::MAX_LENGTH_CODE],
		'description' => ['type' => 'text', 'maxlength' => 20],
	];

	protected static $instance;

/* =============================================================
	Json
============================================================= */
	/**
	 * Return JSON
	 * @param  Code $code
	 * @return array
	 */
	public function codeJson(Code $code) {
		return [
			'system'      => $code->system,
			'sysopcode'   => $code->sysop,
			'id'          => $code->id,
			'code'        => $code->id,
			'description' => $code->description,
		];
	}

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Query Filtered for Sysop Code
	 * @param  string $sysop   System Optional Code
	 * @param  string $id      Code
	 * @return SysopOptionalCode
	 */
	public function querySysopCode($sysop, $id) {
		$q = $this->query();
		$q->filterBySystem(static::SYSTEM);
		$q->filterBySysop($sysop);
		$q->filterById($id);
		return $q;
	}

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return if Sysop Option Exists
	 * @param  string $sysop   System Optional Code
	 * @param  string $id      Code
	 * @return bool
	 */
	public function exists($sysop, $id) {
		$q = $this->querySysopCode($sysop, $id);
		return boolval($q->count());
	}

	/**
	 * Return Sysop Code
	 * @param  string $sysop   System Optional Code
	 * @param  string $id      Code
	 * @return SysopOptionalCode
	 */
	public function code($sysop, $id) {
		$q = $this->querySysopCode($sysop, $id);
		return $q->findOne();
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @param  string $sysop  System Optional Code
	 * @param  string $id      Optional Code
	 * @return SysopOptionalCode
	 */
	public function new($sysop = '', $id = '') {
		$code = new Model();
		$code->setSystem(static::SYSTEM);
		$code->setSysop($sysop);

		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		return $code;
	}

	/**
	 * Return New or Existing Code
	 * @param  string $sysop  System Optional Code
	 * @param  string $id     Optional Code
	 * @return Code
	 */
	public function getOrCreate($sysop = '', $id = '') {
		if ($this->exists($sysop, $id)) {
			return $this->code($sysop, $id);
		}
		return $this->new($sysop, $id);
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

		$code = $this->getOrCreate($values->text('sysop'), $values->text('code'));

		if ($this->lockrecord($code) === false) {
			$msg = "$code->system Code $code->id is Locked By " . $this->recordlocker->getLockingUser($this->getRecordlockerKey($code));
			$this->setResponse(Response::responseError($msg));
			return false;
		}
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
		return $invalidfields;
	}

	/**
	 * Delete Code
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function inputDelete(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		if ($this->exists($values->text('sysop'), $values->text('code')) === false) {
			return true;
		}

		$code = $this->code($values->text('sysop'), $values->text('code'));
		if ($this->lockrecord($code) === false) {
			$msg = "$code->sysop Code $code->id is Locked By " . $this->recordlocker->getLockingUser($this->getRecordlockerKey($code));
			$this->setResponse(Response::responseError($msg));
			return false;
		}
		$code->delete();
		$response = $this->saveAndRespond($code);
		$this->setResponse($response);
		return $response->hasSuccess();
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
		$response->addMsgReplacement("{sysop}", $code->sysop);
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
		$data = ["DBNAME=$dplusdb", 'UPDATEOPTCODE', "SYS=$code->system", "CODE=$code->sysop", "ID=$code->id"];
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
		return implode(FunctionLocker::glue(), [$code->system, $code->sysop, $code->code]);
	}
}
