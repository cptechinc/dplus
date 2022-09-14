<?php namespace Dplus\Codes\Mar;
// Propel Classes
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireInput;
// Dplus Models
use ArPriceCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes;
use Dplus\Codes\AbstractCodeTableEditableSingleKey;

/**
 * Class that handles the CRUD of the CPM code table
 */
class Cpm extends AbstractCodeTableEditableSingleKey {
	const MODEL              = 'ArPriceCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'ar_cust_price';
	const DESCRIPTION        = 'Customer Price Code';
	const DESCRIPTION_RECORD = 'Customer Price Code';
	const RESPONSE_TEMPLATE  = 'Customer Price Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'cpm';
	const DPLUS_TABLE           = 'CPM';
	const FIELD_ATTRIBUTES = [
		'code'             => ['type' => 'text', 'maxlength' => ArPriceCode::MAX_LENGTH_CODE],
		'description'      => ['type' => 'text', 'maxlength' => 20],
		'surcharge'        => ['type' => 'text', 'options' => ['Y' => 'Yes', 'N' => 'No'], 'default' => 'N'],
		'percent'          => ['type' => 'number', 'precision' => 3, 'max' => 99.999, 'default' => '0.000'],
	];

	public function codeJson(Code $code) {
		$json = parent::codeJson($code);
		$json['surcharge'] = $code->surcharge;
		$json['percent']   = $code->percent;
		return $json;
	}

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return IDs
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
	 * Return new ArPriceCode
	 * @param  string $id Code
	 * @return ArPriceCode
	 */
	public function new($id = '') {
		$code = new ArPriceCode();

		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setCode($id);
		}
		$code->setSurcharge($this->fieldAttribute('surcharge', 'default'));
		$code->setPercent($this->fieldAttribute('percent', 'default'));
		return $code;
	}

/* =============================================================
	CRUD Processing
============================================================= */
	/**
	 * Update Record with Input Data
	 * @param  WireInput       $input Input Data
	 * @param  ArPriceCode  $code
	 * @return array
	 */
	protected function _inputUpdate(WireInput $input, Code $code) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$invalidfields   = parent::_inputUpdate($input, $code);		
		$code->setSurcharge($values->yn('surcharge'));
		$code->setPercent($values->float('percent', ['precision' => $this->fieldAttribute('percent', 'precision'), 'max' => $this->fieldAttribute('percent', 'max')]));
		return $invalidfields;
	}
}
