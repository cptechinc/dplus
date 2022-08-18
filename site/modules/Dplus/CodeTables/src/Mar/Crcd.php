<?php namespace Dplus\Codes\Mar;
// Propel Classes
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireInput;
// Dplus Models
use ArCreditCardCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes;
use Dplus\Codes\Base\Simple as Base;
use ProcessWire\WireInputData;

/**
 * Class that handles the CRUD of the CRCD code table
 */
class Crcd extends Base {
	const MODEL 			 = 'ArCreditCardCode';
	const MODEL_KEY 		 = 'id';
	const MODEL_TABLE		 = 'ar_cust_crcd';
	const DESCRIPTION		 = 'Credit Card Code';
	const DESCRIPTION_RECORD = 'Credit Card Code';
	const RESPONSE_TEMPLATE  = 'Credit Card Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'crcd';
	const DPLUS_TABLE			= 'CRCD';
	const FIELD_ATTRIBUTES = [
		'code'				   => ['type' => 'text', 'maxlength' => ArCreditCardCode::MAX_LENGTH_CODE],
		'description'		   => ['type' => 'text', 'maxlength' => 20],
		'gl_account'		   => ['type' => 'text'],
		'custid'			   => ['type' => 'text'],
		'gl_account_charge'    => ['type' => 'text'],
		'servicerate'		   => ['type' => 'number', 'max' => 99.99, 'precision' => 2, 'default' => 0.00],
		'transactioncost'	   => ['type' => 'number', 'max' => 99.99, 'precision' => 2, 'default' => 0.00],
		'cc_surcharge_percent' => ['type' => 'number', 'max' => 99.99, 'precision' => 2, 'default' => 0.00],
	];

	public function codeJson(Code $code) {
		$json = parent::codeJson($code);
		$json['gl_account'] 	   = $code->gl_account;
		$json['custid'] 		   = $code->custid;
		$json['gl_account_charge'] = $code->gl_account_charge;
		$json['servicerate']	   = $code->servicerate;
		$json['transactioncost']	  = $code->transactioncost;
		$json['cc_surcharge_percent'] = $code->cc_surcharge_percent;
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
		$q->select(ArCreditCardCode::aliasproperty('id'));
		return $q->find()->toArray();
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return new ArCreditCardCode
	 * @param  string $id Code
	 * @return ArCreditCardCode
	 */
	public function new($id = '') {
		$this->initFieldAttributes();
		$code = new ArCreditCardCode();

		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		$code->setServicerate($this->fieldAttribute('servicerate', 'default'));
		$code->setTransactioncost($this->fieldAttribute('transactioncost', 'default'));
		$code->setCc_surcharge_percent($this->fieldAttribute('cc_surcharge_percent', 'default'));
		return $code;
	}

/* =============================================================
	CRUD Processing
============================================================= */
	/**
	 * Update Record with Input Data
	 * @param  WireInput	     $input Input Data
	 * @param  ArCreditCardCode  $code
	 * @return array
	 */
	protected function _inputUpdate(WireInput $input, Code $code) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$invalidfields  = parent::_inputUpdate($input, $code);
		$invalidfieldsGL = $this->_inputUpdateCustid($values, $code);
		$invalidfieldsCH = $this->_inputUpdateCharges($values, $code);

		$invalidfields = array_merge($invalidfields, $invalidfieldsGL, $invalidfieldsCH);
		return $invalidfields;
	}

	/**
	 * Update Costs, Charges fields
	 * @param  WireInputData $values
	 * @param  ArCreditCardCode $code
	 * @return array
	 */
	private function _inputUpdateCharges(WireInputData $values, ArCreditCardCode $code) {
		$fields = ['servicerate', 'transactioncost', 'cc_surcharge_percent'];

		foreach ($fields as $field) {
			$fieldOptions = [
				'precision' => $this->fieldAttribute($field, 'precision'),
				'max'       => $this->fieldAttribute($field, 'max')
			];
			$setField = 'set'.ucfirst($field);
			$code->$setField($values->float($field, $fieldOptions));
		}
		return [];
	}

	/**
	 * Update Custid field, return errors if any
	 * @param  WireInputData    $values
	 * @param  ArCreditCardCode $code
	 * @return array
	 */
	private function _inputUpdateCustid(WireInputData $values, ArCreditCardCode $code) {
		$customers = Cmm::getInstance();

		if ($customers->exists($values->text('custid')) === false) {
			return ['custid' => 'Customer ID'];
		}
		$code->setCustid($values->text('custid'));
		return [];
	}

	/**
	 * Update Gl Accounts fields, return errors if any
	 * @param  WireInputData    $values
	 * @param  ArCreditCardCode $code
	 * @return array
	 */
	private function _inputUpdateGlAccounts(WireInputData $values, ArCreditCardCode $code) {
		$glAccounts = Codes\Mgl\Mhm::getInstance();
		$invalidfields = [];

		$code->setGl_account($values->text('gl_account'));

		if ($glAccounts->exists($values->text('gl_account')) === false) {
			$code->setGl_account('');
			$invalidfields['gl_account'] = 'GL Account';
		}

		$code->setGl_account_charge($values->text('gl_account_charge'));

		if ($glAccounts->exists($values->text('gl_account_charge')) === false) {
			$code->setGl_account_charge('');
			$invalidfields['gl_account_charge'] = 'GL Account Charge';
		}
		return $invalidfields;
	}
}
