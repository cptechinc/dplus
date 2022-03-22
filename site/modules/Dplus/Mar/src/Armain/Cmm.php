<?php namespace Dplus\Mar\Armain;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Record;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use CustomerQuery, Customer;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Filters
use Dplus\Filters;
// Dplus Codes
use Dplus\Crud\Manager as Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the Customer Table
 *
 * @property array  $attributes Field Attributes, some defaults are loaded from configs
 */
class Cmm extends Base {
	const MODEL              = 'Customer';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'ar_cust_mast';
	const DESCRIPTION        = 'Customer';
	const DESCRIPTION_RECORD = 'Customer';
	const RESPONSE_TEMPLATE  = 'Customer {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'cmm';
	const DPLUS_TABLE           = 'CMM';
	const FIELD_ATTRIBUTES = [
		'id'       => ['type' => 'text', 'maxlength' => 6],
		'name'     => ['type' => 'text', 'maxlength' => 30],
		'address1' => ['type' => 'text', 'maxlength' => 30],
		'address2' => ['type' => 'text', 'maxlength' => 30],
		'address3' => ['type' => 'text', 'maxlength' => 30],
		'city'     => ['type' => 'text', 'maxlength' => 16],
		'state'    => ['type' => 'text', 'maxlength' => 2],
		'zip'      => ['type' => 'text', 'maxlength' => 10],
		'salesperson1' => ['type' => 'text', 'default' => ''],
		'salesperson2' => ['type' => 'text', 'default' => ''],
		'salesperson3' => ['type' => 'text', 'default' => ''],
		'whseid'       => ['type' => 'text', 'default' => ''],
		'remitwhseid'  => ['type' => 'text', 'default' => ''],
		'taxcode'      => ['type' => 'text', 'default' => ''],
		'termscode'    => ['type' => 'text', 'default' => ''],
		'shipviacode'  => ['type' => 'text', 'default' => ''],
		'typecode'     => ['type' => 'text', 'default' => ''],
		'pricecode'    => ['type' => 'text', 'default' => '', 'disabled' => true],
		'commcode'     => ['type' => 'text', 'default' => '', 'disabled' => true],
		'creditlimit'  => ['type' => 'number', 'max' => 99999999.99, 'precision' => 2, 'default' => 0.00],
		'shipcomplete' => ['type' => 'text', 'default' => 'N'],
		'stmtcode' => [
			'type' => 'text', 'default' => 'S',
			'options' => [
				'S' => 'Statements',
				'I' => 'Invoices',
				'N' => 'Neither',
				'B' => 'Both',
			]
		],
		'allowBackorder' => [
			'type' => 'text', 'default' => 'N',
			'options' => [
				'I' => 'Item Record',
				'N' => 'Never',
				'A' => 'Always',
			]
		],
		'allowFinancecharge' => ['type' => 'text', 'default' => 'N'],
		'additonaldiscount' => ['type' => 'number', 'max' => 99.99, 'precision' => 2, 'default' => 0.00],
	];

	protected static $instance;
	private $fieldAttributes;

/* =============================================================
	Field Configs
============================================================= */

	public function initFieldAttributes() {
		if (empty($this->fieldAttributes) === false) {
			return true;
		}
		$configAR = Configs\Ar::config();

		$attributes = self::FIELD_ATTRIBUTES;
		$attributes['typecode']['default']     = $configAR->defaultCustType;
		$attributes['salesperson1']['default'] = $configAR->defaultSalespersonid;
		$attributes['whseid']['default']       = $configAR->defaultWarehouseid;
		$attributes['remitwhseid']['default']  = $configAR->defaultWarehouseid;
		$attributes['taxcode']['default']      = $configAR->defaultShipviaCode;
		$attributes['termscode']['default']    = $configAR->defaultTermsCode;
		$attributes['shipviacode']['default']  = $configAR->defaultShipviaCode;
		$attributes['pricecode']['default']    = $configAR->defaultPriceCode;
		$attributes['commcode']['default']     = $configAR->defaultCommCode;
		$attributes['stmtcode']['default']     = $configAR->defaultStmtCode;
		$attributes['allowBackorder']['default']     = $configAR->defaultAllowBackorder;
		$attributes['allowFinancecharge']['default'] = $configAR->defaultAllowBackorder;

		$attributes['pricecode']['disabled']   = $configAR->usePriceCode() === false;
		$attributes['commcode']['disabled']    = $configAR->useCommCode() === false;

		$this->fieldAttributes = $attributes;
	}

	/**
	 * Return Field Attribute value
	 * @param  string $field Field Name
	 * @param  string $attr  Attribute Name
	 * @return mixed|bool
	 */
	public function fieldAttribute($field = '', $attr = '') {
		if (empty($field) || empty($attr)) {
			return false;
		}

		$this->initFieldAttributes();

		if (array_key_exists($field, $this->fieldAttributes) === false) {
			return false;
		}
		if (array_key_exists($attr, $this->fieldAttributes[$field]) === false) {
			return false;
		}
		return $this->fieldAttributes[$field][$attr];
	}

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return Query Filtered by Customer ID
	 * @param  string $id Customer ID
	 * @return CustomerQuery
	 */
	public function queryId($id) {
		return $this->query()->filterById($id);
	}

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return if Customer Exists
	 * @param  string $id Customer ID
	 * @return bool
	 */
	public function exists($id) {
		return boolval($this->queryId($id)->count());
	}

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
	 * Return Customer
	 * @param  string $id Customer ID
	 * @return Customer
	 */
	public function customer($id) {
		$q = $this->getQueryClass();
		return $q->findOneById($id);
	}

	/**
	 * Return Array ready for JSON
	 * @param  Record  $record Code
	 * @return array
	 */
	public function recordJson(Record $record) {
		$json = [
			'id'    => $record->id,
			'name'  => $record->name,
		];
		return $json;
	}

	/**
	 * Return New or Existing Customer
	 * @param  string $id Customer ID
	 * @return Customer
	 */
	public function getOrCreate($id) {
		if ($this->exists($id) === false) {
			return $this->new($id);
		}
		return $this->customer($id);
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @return Customer
	 */
	public function new($id = '') {
		$this->initFieldAttributes();

		$customer = new Customer();
		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('id', 'maxlength')]);
			$customer->setId($id);
		}

		// Set Default Values
		foreach ($this->fieldAttributes as $name => $attr) {
			if (array_key_exists('default', $attr)) {
				$setField = 'set' . ucfirst($name);
				$customer->$setField($attr['default']);
			}
		}
		return $customer;
	}

/* =============================================================
	CRUD Processing (UPDATE)
============================================================= */
	/**
	 * Update Record from Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function inputUpdate(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$id     = $values->text('id', ['maxLength' => $this->fieldAttribute('id', 'maxlength')]);
		$invalidfields = [];

		$record        = $this->getOrCreate($id);
		$invalidfields = $this->_inputUpdate($input, $record);
		$response      = $this->saveAndRespond($record, $invalidfields);
		$this->setResponse($response);
		return $response->hasSuccess();
	}


	/**
	 * Update Record with Input Data
	 * @param  WireInput $input Input Data
	 * @param  Record    $record
	 * @return array
	 */
	protected function _inputUpdate(WireInput $input, Record $record) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$invalidfields = [
			'nameAddress' => $this->_inputUpdateNameAddress($input, $record)
		];
		return $invalidfields;
	}

	/**
	 * Update Customer's Name Address Fields
	 * @param  WireInput $input   Input Data
	 * @param  Customer  $customer
	 * @return array
	 */
	protected function _inputUpdateNameAddress(WireInput $input, Customer $customer) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$invalidfields = [];

		$customer->setName($values->text('name', ['maxLength' => $this->fieldAttribute('name', 'maxlength')]));
		$customer->setAddress1($values->text('name', ['maxLength' => $this->fieldAttribute('address1', 'maxlength')]));
		$customer->setAddress2($values->text('name', ['maxLength' => $this->fieldAttribute('address2', 'maxlength')]));
		$customer->setCity($values->text('name', ['maxLength' => $this->fieldAttribute('city', 'maxlength')]));
		$customer->setZip($values->text('zip', ['maxLength' => $this->fieldAttribute('zip', 'maxlength')]));

		$filter = new Filters\Misc\StateCode();

		$customer->setState($values->text('state', ['maxLength' => $this->fieldAttribute('state', 'maxlength')]));

		if ($filters->exists($values->text('state')) === false) {
			$invalidfields['state'] = 'State';
		}

		return $invalidfields;
	}

/* =============================================================
	CRUD Processing (DELETE)
============================================================= */
	/**
	 * Delete Record
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function inputDelete(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$id     = $values->text('id', ['maxLength' => $this->fieldAttribute('id', 'maxlength')]);

		if ($this->exists($id) === false) {
			$response = Response::responseSuccess("Customer $id was deleted");
			$response->buildMessage(static::RESPONSE_TEMPLATE);
			$response->setCode($id);
			return true;
		}
		$customer = $this->customer($id);
		$record->delete();
		$response = $this->saveAndRespond($customer);
		$this->setResponse($response);
		return $response->hasSuccess();
	}

/* =============================================================
	Supplemental
============================================================= */
	public function getStates() {
		$filter = new Filters\Misc\StateCode();
		return $filter->query->find();
	}
}
