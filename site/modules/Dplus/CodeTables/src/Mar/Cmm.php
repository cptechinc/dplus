<?php namespace Dplus\Codes\Mar;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireInput;
// Dplus Models
use Customer;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Codes
use Dplus\Codes\AbstractCodeTableEditableSingleKey;

/**
 * Class that handles the CRUD of the CMM code table
 */
class Cmm extends AbstractCodeTableEditableSingleKey {
	const MODEL              = 'Customer';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'ar_cust_mast';
	const DESCRIPTION        = 'Customer';
	const DESCRIPTION_RECORD = 'Customer';
	const RESPONSE_TEMPLATE  = 'Customer {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'cmm';
	const DPLUS_TABLE           = 'CMM';
	const FIELD_ATTRIBUTES = [
		'id'   => ['type' => 'text', 'maxlength' => 6],
		'name' => ['type' => 'text', 'maxlength' => 20],
	];

	protected static $instance;

	/**
	 * Return Array ready for JSON
	 * @param  Code  $code Code
	 * @return array
	 */
	public function codeJson(Code $code) {
		$json = [
			'id'    => $code->id,
			'name'  => $code->name,
		];
		return $json;
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
		$q->select(Customer::aliasproperty('id'));
		return $q->find()->toArray();
	}

	/**
	 * Return the Code records from Database
	 * @return ObjectCollection
	 */
	public function codes() {
		$q = $this->query();
		return $q->find();
	}

	/**
	 * Return Customer
	 * @param  string $id
	 * @return Customer
	 */
	public function customer($id) {
		return $this->query()->findOneById($id);
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
		return $invalidfields;
	}
}
