<?php namespace Dplus\Codes\Min;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use TariffCodeQuery, TariffCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Filters
use Dplus\Filters;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes;
use Dplus\Codes\Base\Simple as Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the TARM code table
 */
class Tarm extends Base {
	const MODEL              = 'TariffCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'inv_tari_code';
	const DESCRIPTION        = 'Tariff Code';
	const DESCRIPTION_RECORD = 'Tariff Code';
	const RESPONSE_TEMPLATE  = 'Tariff Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'tarm';
	const DPLUS_TABLE           = 'TARM';
	const FIELD_ATTRIBUTES = [
		'code'             => ['type' => 'text', 'maxlength' => TariffCode::MAX_LENGTH_CODE],
		'description'      => ['type' => 'text', 'maxlength' => 30],
		'number'           => ['type' => 'text', 'maxlength' => 12, 'required' => true],
		'percent'          => ['type' => 'number', 'precision' => 2, 'max' => 999.99],
	];

	/** @var self */
	protected static $instance;

	public function __construct() {
		parent::__construct();
		$this->countriesM = Tarm\Countries::getInstance();
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
		$q->select(TariffCode::aliasproperty('id'));
		return $q->find()->toArray();
	}

	/**
	 * Return Array ready for JSON
	 * @param  Code  $code Code
	 * @return array
	 */
	public function codeJson(Code $code) {
		$sanitizer = $this->wire('sanitizer');

		return [
			'code'          => $code->code,
			'description'   => $code->description,
			'number'        => $code->number,
			'percent'       => $sanitizer->float($code->percent, ['precision' => $this->fieldAttribute('percent', 'precision')]),
			'countries'     => $this->countriesM->codesForTariffCode($code->code)
		];
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return new TariffCode
	 * @param  string $id Code
	 * @return TariffCode
	 */
	public function new($id = '') {
		$code = new TariffCode();

		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		return $code;
	}

/* =============================================================
	CRUD Processing
============================================================= */
	/**
	 * Update Tarm from Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function inputUpdate(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values  = $input->$rm;
		$success = parent::inputUpdate($input);
		$id      = $values->text('code', ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
		$code    = $this->code($id);

		if ($success === false) {
			return false;
		}
		if (empty($code)) {
			return $success;
		}
		$this->inputUpdateTariffCountries($input, $code);
	}

	/**
	 * Update Tarm from Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function inputDelete(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values  = $input->$rm;
		$id      = $values->text('code', ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
		$success = parent::inputDelete($input);

		if ($success === false) {
			return false;
		}
		$this->countriesM->deleteForCode($id);
		$code = $this->new($id);
		$this->sendTariffCountryUpdate($code);
	}

	/**
	 * Update Record with Input Data
	 * @param  WireInput $input Input Data
	 * @param  Code      $code
	 * @return array
	 */
	protected function _inputUpdate(WireInput $input, Code $code) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$invalidfields     = parent::_inputUpdate($input, $code);
		$invalidfieldsTarm = $this->inputUpdateTarm($input, $code);
		$invalidfields = array_merge($invalidfields, $invalidfieldsTarm);
		return $invalidfields;
	}

	/**
	 * Update Surcharge fields for record
	 * @param  WireInput    $input Input Data
	 * @param  TariffCode $code
	 * @return array
	 */
	private function inputUpdateTarm(WireInput $input, TariffCode $code) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$invalidfields = [];

		if ($values->text('number') == '') {
			$invalidfields['number'] = 'Tariff Number';
			return $invalidfields;
		}
		$code->setNumber($values->text('number', ['maxLength' => $this->fieldAttribute('number', 'maxlength')]));
		$code->setPercent(
			$values->float('percent', [
					'precision' => $this->fieldAttribute('percent', 'precision'),
					'max' => $this->fieldAttribute('percent', 'max'),
				]
			)
		);

		return $invalidfields;
	}

	/**
	 * Update Tariff Code Countries
	 * @param  WireInput   $input Input Data
	 * @param  TariffCode  $code
	 * @return
	 */
	private function inputUpdateTariffCountries(WireInput $input, TariffCode $code) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$countries = $values->array('country');
		$this->countriesM->deleteForCode($code->code);

		$filterCC = new Filters\Misc\CountryCode();

		foreach ($countries as $country) {
			if ($filterCC->existsIso3($country)) {
				$tariffCountry = $this->countriesM->new($code->code, $country);
				$tariffCountry->setDate(date('Ymd'));
				$tariffCountry->setTime(date('His'));
				$tariffCountry->save();
			}
		}
		$this->sendTariffCountryUpdate($code);
	}

/* =============================================================
	Dplus Requests
============================================================= */
	private function sendTariffCountryUpdate(TariffCode $code) {
		$data = self::generateRequestData($code);
		$data[] = 'CTRY=UPDATED';
		$this->sendDplusRequest($data);
	}
}
