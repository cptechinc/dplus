<?php namespace Dplus\Codes\Mar;
// Propel Classes
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireInput;
use ProcessWire\WireInputData;
// Dplus Models
use ArTaxCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes;
use Dplus\Codes\AbstractCodeTableEditableSingleKey;


/**
 * Class that handles the CRUD of the MTM code table
 */
class Mtm extends AbstractCodeTableEditableSingleKey {
	const MODEL 			 = 'ArTaxCode';
	const MODEL_KEY 		 = 'id';
	const MODEL_TABLE		 = 'ar_cust_mtax';
	const DESCRIPTION		 = 'Master Tax Code';
	const DESCRIPTION_RECORD = 'Master Tax Code';
	const RESPONSE_TEMPLATE  = 'Master Tax Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'mtm';
	const DPLUS_TABLE			= 'MTM';
	const FIELD_ATTRIBUTES = [
		'code'				   => ['type' => 'text', 'maxlength' => ArTaxCode::MAX_LENGTH_CODE],
		'description'		   => ['type' => 'text', 'maxlength' => 20],
		'percent'		       => ['type' => 'number', 'max' => 99.999, 'precision' => 3, 'default' => 0.000],
		'gl_account'		   => ['type' => 'text'],
		'freight_tax_code'     => ['type' => 'text'],
		'limit'		           => ['type' => 'number', 'max' =>  999999, 'precision' => 0, 'default' => ''],
		'note1'			       => ['type' => 'text', 'maxlength' => 50],
		'note2'			       => ['type' => 'text', 'maxlength' => 50],
		'note3'			       => ['type' => 'text', 'maxlength' => 50],
		'note4'			       => ['type' => 'text', 'maxlength' => 50],
		'tax_type'             => ['type' => 'text', 'options' => ['S' => 'State', 'C' => 'County', 'L' => 'Local', 'T' => 'Transit']],
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
		$q->select(ArTaxCode::aliasproperty('id'));
		return $q->find()->toArray();
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return new ArTaxCode
	 * @param  string $id Code
	 * @return ArTaxCode
	 */
	public function new($id = '') {
		$this->initFieldAttributes();
		$code = new ArTaxCode();

		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->string($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		$code->setPercent($this->fieldAttribute('percent', 'default'));
		$code->setLimit($this->fieldAttribute('limit', 'default'));
		return $code;
	}

/* =============================================================
	CRUD Processing
============================================================= */
	/**
	 * Update Record with Input Data
	 * @param  WireInput  $input Input Data
	 * @param  ArTaxCode  $code
	 * @return array
	 */
	protected function _inputUpdate(WireInput $input, Code $code) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$invalidfields  =  parent::_inputUpdate($input, $code);
		$invalidfieldsTX = $this->_inputUpdateTax($values, $code);
		$invalidfieldsFT = $this->_inputUpdateFreightTaxCode($values, $code);
		$invalidfieldsGL = $this->_inputUpdateGlAccount($values, $code);
		$invalidfieldsNT = $this->_inputUpdateNotes($values, $code);

		$invalidfields = array_merge($invalidfields, $invalidfieldsTX, $invalidfieldsFT, $invalidfieldsGL, $invalidfieldsNT);
		return $invalidfields;
	}

	/**
	 * Update Percent, Taxable Limit fields
	 * @param  WireInputData  $values
	 * @param  ArTaxCode      $code
	 * @return array
	 */
	private function _inputUpdateTax(WireInputData $values, ArTaxCode $code) {
		$opts = [
			'max' => $this->fieldAttribute('percent', 'max'),
			'min' => $this->fieldAttribute('percent', 'default'),
			'precision' => $this->fieldAttribute('percent', 'precision'),
		];
		$code->setPercent($values->float('percent', $opts));

		$opts = [
			'max' => $this->fieldAttribute('limit', 'max'),
			'precision' => $this->fieldAttribute('limit', 'recision'),
		];
		$limit = $values->float('limit', $opts);
		if ($limit == 0) {
			$limit = '';
		}
		$code->setLimit($limit);

		$taxType = $values->text('tax_type');
		if (array_key_exists($taxType, $this->fieldAttribute('tax_type', 'options'))) {
			$code->setTax_type($taxType);
		}
		return [];
	}


	/**
	 * Update Gl Account field, return errors if any
	 * @param  WireInputData  $values
	 * @param  ArTaxCode      $code
	 * @return array
	 */
	private function _inputUpdateGlAccount(WireInputData $values, ArTaxCode $code) {
		$glAccounts = Codes\Mgl\Mhm::getInstance();
		$invalidfields = [];

		$code->setGl_account($values->text('gl_account'));

		if ($glAccounts->exists($values->text('gl_account')) === false) {
			$code->setGl_account('');
			$invalidfields['gl_account'] = 'GL Account';
		}
		return $invalidfields;
	}

	/**
	 * Update Freight Tax Code
	 * @param  WireInputData  $values
	 * @param  ArTaxCode      $code
	 * @return array
	 */
	private function _inputUpdateFreightTaxCode(WireInputData $values, ArTaxCode $code) {
		$invalidfields = [];

		if ($values->text('freight_tax_code') == $code->code) {
			return $invalidfields;
		}

		$code->setFreight_tax_code($values->text('freight_tax_code'));

		if ($this->exists($values->text('freight_tax_code')) === false) {
			$code->setFreight_tax_code('');
			$invalidfields['freight_tax_code'] = 'Freight Tax Code';
		}
		return $invalidfields;
	}

	/**
	 * Update Notes
	 * @param  WireInputData  $values
	 * @param  ArTaxCode     $code
	 * @return array
	 */
	private function _inputUpdateNotes(WireInputData $values, ArTaxCode $code) {
		for ($i = 1; $i <= 4; $i++) {
			$field = "note$i";
			$setField = 'set' . ucfirst($field);

			$code->$setField($values->text($field, ['maxLength' => $this->fieldAttribute($field, 'maxlength')]));
		}
		return [];
	}

/* =============================================================
	Supplemental
============================================================= */
	/**
	 * Return GL Account Description
	 * @param  string $id GL Code
	 * @return string
	 */
	public function glAcctDescription($id) {
		$mhm = Codes\Mgl\Mhm::getInstance();
		return $mhm->exists($id) ? $mhm->description($id) : '';
	}
}
