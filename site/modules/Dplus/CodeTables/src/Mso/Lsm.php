<?php namespace Dplus\Codes\Mso;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use LostSalesCodeQuery, LostSalesCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes\AbstractCodeTableEditableSingleKey;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the LSM code table
 */
class Lsm extends AbstractCodeTableEditableSingleKey {
	const MODEL              = 'LostSalesCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'so_lssl_code';
	const DESCRIPTION        = 'Lost Sales Reason Code';
	const DESCRIPTION_RECORD = 'Lost Sales Reason Code';
	const RESPONSE_TEMPLATE  = 'Lost Sales Reason Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'lsm';
	const DPLUS_TABLE           = 'LSM';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => LostSalesCode::MAX_LENGTH_CODE],
		'description' => ['type' => 'text', 'maxlength' => 20],
	];

	protected static $instance;

	private $fieldAttributes;

	/**
	 * Return Array ready for JSON
	 * @param  Code  $code Code
	 * @return array
	 */
	public function codeJson(Code $code) {
		return [
			'code'                 => $code->code,
			'description'          => $code->description,
			'updateinventorywatch' => $code->updateinventorywatch,
		];
	}

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return the IDs for the Work Center Confirm Code
	 * @return array
	 */
	public function ids() {
		$q = $this->query();
		$q->select(LostSalesCode::aliasproperty('id'));
		return $q->find()->toArray();
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @return LostSalesCode
	 */
	public function new($id = '') {
		$code = new LostSalesCode();
		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->string($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		return $code;
	}

/* =============================================================
	CRUD Processing
============================================================= */
	/**
	 * Update Record with Input Data
	 * @param  WireInput $input Input Data
	 * @param  Code      $code
	 * @return array
	 */
	protected function _inputUpdate(WireInput $input, Code $code) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$invalidfields = parent::_inputUpdate($input, $code);
		$code->setUpdateinventorywatch($values->yn('updateinventorywatch'));
		return $invalidfields;
	}
}
