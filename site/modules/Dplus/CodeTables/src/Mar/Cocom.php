<?php namespace Dplus\Codes\Mar;
// Propel Classes
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireInput;
// Dplus Models
use CountryCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes;
use Dplus\Codes\Base\Simple as Base;
use ProcessWire\WireInputData;

/**
 * Class that handles the CRUD of the COCOM code table
 */
class Cocom extends Base {
	const MODEL              = 'CountryCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'country_codes';
	const DESCRIPTION        = 'Country / Currency Code';
	const DESCRIPTION_RECORD = 'Country / Currency Code';
	const RESPONSE_TEMPLATE  = 'Country / Currency Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'cocom';
	const DPLUS_TABLE           = 'COCOM';
	const FIELD_ATTRIBUTES = [
		'code'             => ['type' => 'text', 'maxlength' => 3, 'case' => 'upper'],
		'description'      => ['type' => 'text', 'maxlength' => 25],
		'iso2'             => ['type' => 'text', 'maxlength' => 3, 'case' => 'upper'],
		'numeric'          => ['type' => 'number', 'max' => 999, 'precision' => 0],
		'custom_code'      => ['type' => 'text', 'maxlength' => 4],
		'exchange_rate'    => ['type' => 'number', 'precision' => 4, 'max' => 9999999.9999, 'default' => 1.0000, 'min' => 1.0000],
		'country_date'     => ['type' => 'text', 'dateformat' => 'Ymd', 'displayformat' => 'm/d/Y'],
	];

	public function codeJson(Code $code) {
		$json = parent::codeJson($code);
		foreach (array_keys(self::FIELD_ATTRIBUTES) as $field) {
			$json[$field] = $code->$field;
		}
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
		$q->select(CountryCode::aliasproperty('id'));
		return $q->find()->toArray();
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return new CountryCode
	 * @param  string $id Code
	 * @return CountryCode
	 */
	public function new($id = '') {
		$this->initFieldAttributes();
		$code = new CountryCode();

		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		$code->setExchange_rate($this->fieldAttribute('exchange_rate', 'default'));
		$code->setCountry_date(date($this->fieldAttribute('country_date', 'dateformat')));
		return $code;
	}

/* =============================================================
	CRUD Processing
============================================================= */
	/**
	 * Update Record with Input Data
	 * @param  WireInput       $input Input Data
	 * @param  CountryCode  $code
	 * @return array
	 */
	protected function _inputUpdate(WireInput $input, Code $code) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$invalidfields     = parent::_inputUpdate($input, $code);
		$invalidfieldsIso  = $this->_inputUpdateIsoCodes($values, $code);
		$invalidfieldsRate = $this->_inputUpdateExchangeRate($values, $code);
		return $invalidfields;
	}

	/**
	 * Update ISO Codes
	 * NOTE: set proper case for Code (ISO3), ISO2
	 * @param  WireInputData $values
	 * @param  CountryCode   $code
	 * @return array
	 */
	protected function _inputUpdateIsoCodes(WireInputData $values, CountryCode $code) {
		$code->setCode($values->text('code', ['maxlength' => $this->fieldAttribute('code', 'maxlength')]));
		$code->setCode(strtoupper($code->id));

		$code->setIso2($values->text('iso2', ['maxlength' => $this->fieldAttribute('iso2', 'maxlength')]));
		$code->setIso2(strtoupper($code->iso2));

		$code->setCustom_code($values->text('custom_code', ['maxlength' => $this->fieldAttribute('custom_code', 'maxlength')]));

		if ($values->int('numeric') > 0) {
			$code->setNumeric($values->int('numeric', ['max', $this->fieldAttribute('numeric', 'max')]));
		}
		return [];
	}

	/**
	 * Update Exchange Rate, date
	 * @param  WireInputData $values
	 * @param  CountryCode   $code
	 * @return array
	 */
	protected function _inputUpdateExchangeRate(WireInputData $values, CountryCode $code) {
		$opts = [
			'min' => $this->fieldAttribute('exchange_rate', 'min'),
			'max' => $this->fieldAttribute('exchange_rate', 'max'),
			'blankValue' => $this->fieldAttribute('exchange_rate', 'default'),
			'precision'  => $this->fieldAttribute('exchange_rate', 'precision'),
		];
		$currentRate = $this->sanitizer->float($code->exchange_rate, $opts);
		$newRate     = $values->float('exchange_rate', $opts);

		if ($currentRate == $newRate) {
			return [];
		}
		$code->setExchange_rate($newRate);
		$code->setCountry_date(date($this->fieldAttribute('country_date', 'dateformat')));
		return [];
	}
}
