<?php namespace Dplus\Codes\Mar;
// Propel Classes
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireInput;
use ProcessWire\WireInputData;
// Dplus Models
use ArCustTaxCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes;
use Dplus\Codes\AbstractCodeTableEditableSingleKey;


/**
 * Class that handles the CRUD of the TM code table
 */
class Tm extends AbstractCodeTableEditableSingleKey {
	const MODEL              = 'ArCustTaxCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'ar_cust_ctax';
	const DESCRIPTION        = 'Customer Tax Code';
	const DESCRIPTION_RECORD = 'Customer Tax Code';
	const RESPONSE_TEMPLATE  = 'Customer Tax Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'tm';
	const DPLUS_TABLE           = 'TM';
	const FIELD_ATTRIBUTES = [
		'code'             => ['type' => 'text', 'maxlength' => ArCustTaxCode::MAX_LENGTH_CODE],
		'description'      => ['type' => 'text', 'maxlength' => 20],
	];
	const NUMBER_TAXCODES = ArCustTaxCode::NUMBER_TAXCODES;

	/**
	 * Return JSON array
	 * @param  ArCustTaxCode $code
	 * @return array
	 */
	public function codeJson(Code $code) {
		$json = parent::codeJson($code);
		for ($i = 1; $i < self::NUMBER_TAXCODES + 1; $i++) {
			$json["taxcode$i"] = $code->taxcode($i);
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
		$q->select(ArCustTaxCode::aliasproperty('id'));
		return $q->find()->toArray();
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return new ArCustTaxCode
	 * @param  string $id Code
	 * @return ArCustTaxCode
	 */
	public function new($id = '') {
		$this->initFieldAttributes();
		$code = new ArCustTaxCode();

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
	 * Update Record with Input Data
	 * @param  WireInput       $input Input Data
	 * @param  ArCustTaxCode  $code
	 * @return array
	 */
	protected function _inputUpdate(WireInput $input, Code $code) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$invalidfields  = parent::_inputUpdate($input, $code);
		$invalidfieldsTx = $this->_inputUpdateTaxCodes($values, $code);
		return array_merge($invalidfields, $invalidfieldsTx);
	}

	/**
	 * Update Tax Codes
	 * @param  WireInputData $values
	 * @param  ArCustTaxCode $code
	 * @return array
	 */
	private function _inputUpdateTaxCodes(WireInputData $values, ArCustTaxCode $code) {
		$mtm = Mtm::instance();
		$taxcodes = [];
		
		for ($i = 1; $i <= ArCustTaxCode::NUMBER_TAXCODES; $i++) {
			$code->setTaxcode($i, '');
			$taxcode = $values->text('taxcode' . $i);

			if (empty($taxcode)) {
				continue;
			}

			if ($mtm->exists($taxcode) === false || in_array($taxcode, $taxcodes)) {
				continue;
			}
			$code->setTaxcode($i, $taxcode);
			$taxcodes[] = $taxcode;
		}
		return [];
	}
}
