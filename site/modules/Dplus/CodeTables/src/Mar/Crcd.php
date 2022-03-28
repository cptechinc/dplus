<?php namespace Dplus\Codes\Mar;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use ArCreditCardCodeQuery, ArCreditCardCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes;
use Dplus\Codes\Base\Simple as Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the CRCD code table
 */
class Crcd extends Base {
	const MODEL              = 'ArCreditCardCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'ar_cust_crcd';
	const DESCRIPTION        = 'Credit Card';
	const DESCRIPTION_RECORD = 'Credit Card';
	const RESPONSE_TEMPLATE  = 'Credit Card {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'crcd';
	const DPLUS_TABLE           = 'CRCD';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => ArCreditCardCode::MAX_LENGTH_CODE],
		'description' => ['type' => 'text', 'maxlength' => 20],
		'custid'      => ['type' => 'text', 'required' => true],
		'servicerate' => ['type' => 'number', 'precision' => 2, 'max' => '99.99', 'default' => 0.00],
		'transactioncost'    => ['type' => 'number', 'precision' => 2, 'max' => '99.99', 'default' => 0.00],
		'surchargepercentcc' => ['type' => 'number', 'precision' => 2, 'max' => '99.99', 'default' => 0.00],
	];

	/** @var self */
	protected static $instance;

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return Array ready for JSON
	 * @param  Code  $code Code
	 * @return array
	 */
	public function codeJson(Code $code) {
		return [
			'code'        => $code->code,
			'description' => $code->description,
			'custid'      => $code->custid,
			'servicerate' => $code->servicerate,
			'transactioncost' => $code->transactioncost,
			'surchargepercentcc' => $code->surchargepercentcc,
			'glAcct' => [
				'id' => $code->glaccountnbr,
			],
			'glAcctCharge' => [
				'id' => $code->glchargeaccountnbr,
			],
		];
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @return ArCreditCardCode
	 */
	public function new($id = '') {
		$code = new ArCreditCardCode();
		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		$code->setServicerate($this->fieldAttribute('servicerate', 'default'));
		$code->setTransactioncost($this->fieldAttribute('transactioncost', 'default'));
		$code->setSurchargepercentcc($this->fieldAttribute('surchargepercentcc', 'default'));
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

		$invalidfields     = parent::_inputUpdate($input, $code);
		$invalidfieldsCrcd = $this->_inputUpdateCrcd($input, $code);
		$invalidfields     = array_merge($invalidfields, $invalidfieldsCrcd);
		return $invalidfields;
	}

	/**
	 * Update ArCreditCardCode's fields
	 * @param  WireInput        $input Input Data
	 * @param  ArCreditCardCode $code
	 * @return array            Field(s) with Errors
	 */
	protected function _inputUpdateCrcd(WireInput $input, ArCreditCardCode $code) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$invalidfields   = $this->_inputUpdateCharges($input, $code);
		$invalidfieldsGl = $this->_inputUpdateGlAccts($input, $code);
		$invalidfieldsCm = $this->_inputUpdateCust($input, $code);
		$invalidfields   = array_merge($invalidfields, $invalidfieldsGl, $invalidfieldsCm);
		return $invalidfields;
	}

	/**
	 * Update ArCreditCardCode's Cust ID if valid
	 * @param  WireInput        $input Input Data
	 * @param  ArCreditCardCode $code
	 * @return array            Field(s) with Errors
	 */
	protected function _inputUpdateCust(WireInput $input, ArCreditCardCode $code) {
		$rm     = strtolower($input->requestMethod());
		$values = $input->$rm;
		$invalidfields = [];

		$cmm = Codes\Mar\Cmm::getInstance();

		if ($cmm->exists($values->text('custid')) === false) {
			$invalidfields['custid'] = "Customer ID";
		} else {
			$code->setCustid($values->text('custid'));
		}
		return $invalidfields;
	}

	/**
	 * Update ArCreditCardCode's GL Accts after validation
	 * @param  WireInput        $input Input Data
	 * @param  ArCreditCardCode $code
	 * @return array            Field(s) with Errors
	 */
	protected function _inputUpdateGlAccts(WireInput $input, ArCreditCardCode $code) {
		$rm     = strtolower($input->requestMethod());
		$values = $input->$rm;
		$invalidfields = [];

		$mhm = Codes\Mgl\Mhm::getInstance();

		if ($mhm->exists($values->text('glaccountnbr')) === false) {
			$invalidfields['glaccountnbr'] = "General Ledger Account";
		} else {
			$code->setGlaccountnbr($values->text('glaccountnbr'));
		}

		if ($mhm->exists($values->text('glchargeaccountnbr')) === false) {
			$invalidfields['glchargeaccountnbr'] = "General Ledger Charge Account";
		} else {
			$code->setGlchargeaccountnbr($values->text('glchargeaccountnbr'));
		}
		return $invalidfields;
	}

	/**
	 * Update ArCreditCardCode's Charges Data
	 * @param  WireInput        $input Input Data
	 * @param  ArCreditCardCode $code
	 * @return array            Field(s) with Errors
	 */
	protected function _inputUpdateCharges(WireInput $input, ArCreditCardCode $code) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$sanitizerOptions = [
			'servicerate' => [
				'max'      => $this->fieldAttribute('servicerate', 'max'),
				'precision' => $this->fieldAttribute('servicerate', 'precision')
			],
			'transactioncost' => [
				'max'      => $this->fieldAttribute('transactioncost', 'max'),
				'precision' => $this->fieldAttribute('transactioncost', 'precision')
			],
			'surchargepercentcc' => [
				'max'      => $this->fieldAttribute('surchargepercentcc', 'max'),
				'precision' => $this->fieldAttribute('surchargepercentcc', 'precision')
			],
		];
		$code->setServicerate($values->float('servicerate', $sanitizerOptions['servicerate']));
		$code->setTransactioncost($values->float('transactioncost', $sanitizerOptions['transactioncost']));
		$code->setSurchargepercentcc($values->float('surchargepercentcc', $sanitizerOptions['surchargepercentcc']));
		return [];
	}
}
