<?php namespace Dplus\Codes\Mar;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use ArWriteOffCodeQuery, ArWriteOffCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes;
use Dplus\Codes\Base\Simple as Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the WORM code table
 */
class Worm extends Base {
	const MODEL              = 'ArWriteOffCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'ar_cust_woff';
	const DESCRIPTION        = 'Write-Off Reason Code';
	const DESCRIPTION_RECORD = 'Write-Off Reason Code';
	const RESPONSE_TEMPLATE  = 'Write-Off Reason Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'worm';
	const DPLUS_TABLE           = 'WORM';
	const FIELD_ATTRIBUTES = [
		'code'             => ['type' => 'text', 'maxlength' => ArWriteOffCode::MAX_LENGTH_CODE],
		'description'      => ['type' => 'text', 'maxlength' => 20],
		'writeoff'         => ['type' => 'text', 'options' => ['Y' => 'Yes', 'N' => 'No'], 'default' => 'N'],
	];

	private $fieldAttributes;

	public function codeJson(Code $code) {
		$json = parent::codeJson($code);
		$json['writeoff'] = $code->writeoff;
		return $json;
	}

/* =============================================================
	Field Configs
============================================================= */
	public function initFieldAttributes() {
		$attributes = self::FIELD_ATTRIBUTES;
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

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return the IDs for the Work Center Confirm Code
	 * @return array
	 */
	public function ids() {
		$q = $this->query();
		$q->select(ArWriteOffCode::aliasproperty('id'));
		return $q->find()->toArray();
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return new ArWriteOffCode
	 * @param  string $id Code
	 * @return ArWriteOffCode
	 */
	public function new($id = '') {
		$this->initFieldAttributes();
		$code = new ArWriteOffCode();

		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		$code->setWriteoff($this->fieldAttribute('writeoff', 'default'));
		return $code;
	}

/* =============================================================
	CRUD Processing
============================================================= */
	/**
	 * Update Record with Input Data
	 * @param  WireInput       $input Input Data
	 * @param  ArWriteOffCode  $code
	 * @return array
	 */
	protected function _inputUpdate(WireInput $input, Code $code) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$invalidfields   = parent::_inputUpdate($input, $code);
		$code->setWriteoff($values->yn('writeoff'));
		return $invalidfields;
	}
}
