<?php namespace Dplus\Codes\Min;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use MsdsCodeQuery, MsdsCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes\Base\Simple as Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the MSDSM code table
 */
class Msdsm extends Base {
	const MODEL              = 'MsdsCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'inv_msds_code';
	const DESCRIPTION        = 'Inventory Material Safety Data Sheet Code';
	const DESCRIPTION_RECORD = 'Inventory Material Safety Data Sheet Code';
	const RESPONSE_TEMPLATE  = 'Inventory Material Safety Data Sheet Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'msdsm';
	const DPLUS_TABLE           = 'MSDSM';
	const FIELD_ATTRIBUTES = [
		'code'          => ['type' => 'text', 'maxlength' => MsdsCode::MAX_LENGTH_CODE],
		'description'   => ['type' => 'text', 'maxlength' => 20],
		'effectivedate' => ['type' => 'text', 'dateformat' => 'Ymd', 'displayformat' => 'm/d/Y'],
	];

	/**
	 * Return Array ready for JSON
	 * @param  Code  $code Code
	 * @return array
	 */
	public function codeJson(Code $code) {
		return [
			'code'          => $code->code,
			'description'   => $code->description,
			'effectivedate' => $code->effectivedate,
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
		$q->select(MsdsCode::aliasproperty('id'));
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
		$invalidfields     = parent::_inputUpdate($input, $code);
		$invalidfieldsMsds = $this->inputUpdateMsds($input, $code);
		$invalidfields = array_merge($invalidfields, $invalidfieldsMsds);
		return $invalidfields;
	}

	/**
	 * Update MsdsCode fields
	 * @param  WireInput $input Input Data
	 * @param  MsdsCode  $code
	 * @return array
	 */
	private function inputUpdateMsds(WireInput $input, MsdsCode $code) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$invalidfields = [];

		$date = $values->date('effectivedate', $format = $this->fieldAttribute('effectivedate', 'displayformat'), ['returnFormat' => $this->fieldAttribute('effectivedate', 'dateformat')]);
		$code->setEffectivedate($date);
		return $invalidfields;
	}


/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @return MsdsCode
	 */
	public function new($id = '') {
		$code = new MsdsCode();
		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		$code->setEffectivedate(date($this->fieldAttribute('effectivedate', 'dateformat')));
		return $code;
	}
}
