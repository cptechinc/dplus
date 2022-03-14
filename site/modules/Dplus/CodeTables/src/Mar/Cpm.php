<?php namespace Dplus\Codes\Mar;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use ArPriceCodeQuery, ArPriceCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes;
use Dplus\Codes\Base\Simple as Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the wORM code table
 */
class Cpm extends Base {
	const MODEL              = 'ArPriceCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'ar_cust_price';
	const DESCRIPTION        = 'Customer Price Code';
	const DESCRIPTION_RECORD = 'Customer Price Code';
	const RESPONSE_TEMPLATE  = 'Customer Price Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'cpm';
	const DPLUS_TABLE           = 'CPM';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => ArPriceCode::MAX_LENGTH_CODE],
		'description' => ['type' => 'text', 'maxlength' => 20],
		'surcharge'   => ['type' => 'text', 'default' => 'N', 'required' => true],
		'percent'     => ['type' => 'number', 'precision' => 3, 'max' => 99.999],
	];

	protected static $instance;

	/**
	 * Return Array ready for JSON
	 * @param  Code  $code Code
	 * @return array
	 */
	public function codeJson(Code $code) {
		return [
			'code'        => $code->code,
			'description' => $code->description,
			'surcharge'   => $code->isSurcharge(),
			'surchargeYN' => $code->surcharge,
			'percent'     => $code->percent
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
		$q->select(ArPriceCode::aliasproperty('id'));
		return $q->find()->toArray();
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @return ArPriceCode
	 */
	public function new($id = '') {
		$code = new ArPriceCode();
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
	 * @param  WireInput $input Input Data
	 * @param  Code      $code
	 * @return array
	 */
	protected function _inputUpdate(WireInput $input, Code $code) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$invalidfields = parent::_inputUpdate($input, $code);
		$code->setSurcharge($values->yn('surcharge'));
		$code->setPercent(0.000);

		if ($code->isSurcharge()) {
			$format = ['precision' => $this->fieldAttribute('percent', 'precision'), 'max' => $this->fieldAttribute('percent', 'max')];
			$code->setPercent($values->float('percent', $format));
		}
		return $invalidfields;
	}
}
