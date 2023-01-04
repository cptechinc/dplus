<?php namespace Dplus\Codes\Mso;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use SoRgaCodeQuery, SoRgaCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes;
use Dplus\Codes\AbstractCodeTableEditableSingleKey;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the RGASC code table
 */
class Rgasc extends AbstractCodeTableEditableSingleKey {
	const MODEL              = 'SoRgaCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'so_rgas_code';
	const DESCRIPTION        = 'RGA/Return Ship Via Code';
	const DESCRIPTION_RECORD = 'RGA/Return Ship Via Code';
	const RESPONSE_TEMPLATE  = 'RGA/Return Ship Via Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'rgasc';
	const DPLUS_TABLE           = 'RGASC';
	const FIELD_ATTRIBUTES = [
		'code'         => ['type' => 'text', 'maxlength' => SoRgaCode::MAX_LENGTH_CODE],
		'description'  => ['type' => 'text', 'maxlength' => 35],
		'acctnbr'      => ['type' => 'text', 'maxlength' => 10],
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
			'whseid'      => $code->whseid,
			'acctnbr'     => $code->acctnbr,
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
		$q->select(SoRgaCode::aliasproperty('id'));
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
		$iwhm = Codes\Min\Iwhm::getInstance();

		if ($iwhm->exists($values->text('whseid')) === false) {
			$invalidfields['whseid'] = "Warehouse ID";
			return $invalidfields;
		}
		$code->setWhseid($values->text('whseid'));
		return $invalidfields;
	}
}
