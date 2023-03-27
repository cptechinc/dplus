<?php namespace Dplus\Codes\Mso;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use  ProcessWire\WireInput;
use  ProcessWire\WireInputData;
// Dplus Models
use MotorFreightCodeQuery, MotorFreightCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes\AbstractCodeTableEditableSingleKey;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the MFCM code table
 */
class Mfcm extends AbstractCodeTableEditableSingleKey {
	const MODEL              = 'MotorFreightCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'so_mfrt_code';
	const DESCRIPTION        = 'Motor Freight Code';
	const DESCRIPTION_RECORD = 'Motor Freight Code';
	const RESPONSE_TEMPLATE  = 'Motor Freight Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'mfcm';
	const DPLUS_TABLE           = 'MFCM';
	const FIELD_ATTRIBUTES = [
		'code'         => ['type' => 'text', 'maxlength' => MotorFreightCode::MAX_LENGTH_CODE],
		'description'  => ['type' => 'text', 'maxlength' => 50],
		'description2' => ['type' => 'text', 'maxlength' => 50],
		'description3' => ['type' => 'text', 'maxlength' => 50],
		'description4' => ['type' => 'text', 'maxlength' => 50],
		'class'        => ['type' => 'text', 'maxlength' => 4],
	];

	protected static $instance;

	/**
	 * Return Array ready for JSON
	 * @param  Code  $code Code
	 * @return array
	 */
	public function codeJson(Code $code) {
		return [
			'code'         => $code->code,
			'class'        => $code->class,
			'description'  => $code->description,
			'description2' => $code->description2,
			'description3' => $code->description3,
			'description4' => $code->description4,
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
		$q->select(MotorFreightCode::aliasproperty('id'));
		return $q->find()->toArray();
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

		$this->_inputUpdateMfcmFields($values, $code);
		return $invalidfields;
	}

	/**
	 * Update Mfcm Record Specific fields
	 * @param  WireInputData  $values
	 * @param  SoRgaCode      $code
	 * @return void
	 */
	private function _inputUpdateMfcmFields(WireInputData $values, Code $code) {
		$fields = ['description2', 'description3', 'description4', 'class'];

		foreach ($fields as $field) {
			$value = $values->string($field);
			$valueMaxLength = substr($value, 0, $this->fieldAttribute($field, 'maxlength'));

			$setField = 'set' . ucfirst($field);
			$code->$setField($valueMaxLength);
		}
	}
}
