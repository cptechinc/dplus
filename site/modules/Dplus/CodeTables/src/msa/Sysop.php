<?php namespace Dplus\Codes\Msa;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// Dplus Models
use MsaSysopCodeQuery, MsaSysopCode;
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
	const RESPONSE_TEMPLATE  = 'System Optional Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'sysop';
	const DPLUS_TABLE           = 'SYSOP';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => MsaSysopCode::MAX_LENGTH_CODE],
		'description' => ['type' => 'text', 'maxlength' => 40],
	];

	protected static $instance;

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return the Code records from Database
	 * @return ObjectCollection
	 */
	public function codes() {
		$q = $this->getQueryClass();
		return $q->find();
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @return MsaSysopCode
	 */
	public function new($id = '') {
		$code = new MsaSysopCode();
		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		return $code;
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
}
