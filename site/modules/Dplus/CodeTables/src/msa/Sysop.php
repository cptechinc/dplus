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
	const RESPONSE_TEMPLATE  = 'System Optional Code {code} {not} {crud}';
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
		'description' => ['type' => 'text', 'maxlength' => 40],
	];

	protected static $instance;

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

	}

	/**
	 * Update Record with Input Data
	 * @param  WireInput $input Input Data
	 * @param  Code      $code
	 * @return array
	 */
	protected function _inputUpdate(WireInput $input, Code $code) {

	}


	/**
	 * Delete Code
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function inputDelete(WireInput $input) {

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
