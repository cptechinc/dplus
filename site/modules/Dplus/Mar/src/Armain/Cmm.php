<?php namespace Dplus\Mar\Armain;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Record;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use CustomerQuery, Customer;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as FunctionLocker;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Filters
use Dplus\Filters;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes;
// Dplus Crud
use Dplus\Crud\Manager as Base;
use Dplus\Crud\Response;


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
	const RESPONSE_TEMPLATE  = 'Customer {key} {not} {crud}';
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
		'taxexemptnbr' => ['type' => 'text', 'maxlength' => 20],
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
		'additionaldiscount' => ['type' => 'number', 'max' => 99.99, 'precision' => 2, 'default' => 0.00],
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
	 * Return Customer
	 * @param  string $id Customer ID
	 * @return Customer
	 */
	public function record($id) {
		return $this->customer($id);
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
			'nameAddress' => $this->_inputUpdateNameAddress($input, $record),
			'warehouses'  => $this->_inputUpdateWarehouses($input, $record),
			'salesreps'   => $this->_inputUpdateSalespersons($input, $record),
			'taxes'       => $this->_inputUpdateTaxes($input, $record),
		];

		$invalid = array_merge($invalidfields['nameAddress'], $invalidfields['warehouses'], $invalidfields['salesreps'], $invalidfields['taxes']);
		return $invalid;
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
		$customer->setAddress1($values->text('address1', ['maxLength' => $this->fieldAttribute('address1', 'maxlength')]));
		$customer->setAddress2($values->text('address2', ['maxLength' => $this->fieldAttribute('address2', 'maxlength')]));
		$customer->setAddress3($values->text('address2', ['maxLength' => $this->fieldAttribute('address3', 'maxlength')]));
		$customer->setCity($values->text('city', ['maxLength' => $this->fieldAttribute('city', 'maxlength')]));
		$customer->setZip($values->text('zip', ['maxLength' => $this->fieldAttribute('zip', 'maxlength')]));
		$customer->setState('');
		$customer->setCountry('');


		$filter = new Filters\Misc\StateCode();

		if ($filter->exists($values->text('state'))) {
			$customer->setState($values->text('state', ['maxLength' => $this->fieldAttribute('state', 'maxlength')]));
		}

		$cocom = Codes\Mar\Cocom::getinstance();

		if ($cocom->exists($values->text('country'))) {
			$customer->setCountry($values->text('country'));
		}
		return $invalidfields;
	}

	/**
	 * Update Customer's Warehouses Fields
	 * @param  WireInput $input   Input Data
	 * @param  Customer  $customer
	 * @return array
	 */
	protected function _inputUpdateWarehouses(WireInput $input, Customer $customer) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$invalidfields = [];

		$iwhm = Codes\Min\Iwhm::getInstance();

		$customer->setWhseid($values->text('whseid'));
		$customer->setRemitwhseid($values->text('remitwhseid'));

		if ($iwhm->exists($customer->whseid) === false) {
			$invalidfields['whseid'] = 'Warehouse';
		}

		if ($iwhm->exists($customer->remitwhseid) === false) {
			$invalidfields['remitwhseid'] = 'Remit Warehouse';
		}
		return $invalidfields;
	}

	/**
	 * Update Customer's Salesperson(1,2,3) Fields
	 * @param  WireInput $input   Input Data
	 * @param  Customer  $customer
	 * @return array
	 */
	protected function _inputUpdateSalespersons(WireInput $input, Customer $customer) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$invalidfields = [];

		$spm = Codes\Mar\Spm::getInstance();

		$customer->setSalesperson1($values->text('salesperson1'));
		$customer->setSalesperson2($values->text('salesperson2'));
		$customer->setSalesperson3($values->text('salesperson3'));

		if ($spm->exists($customer->salesperson1) === false) {
			$invalidfields['salesperson1'] = 'Salesperson 1';
		}

		if ($customer->salesperson2 != '' && $spm->exists($customer->salesperson2) === false) {
			$invalidfields['salesperson2'] = 'Salesperson 2';
		}

		if ($customer->salesperson3 != '' && $spm->exists($customer->salesperson3) === false) {
			$invalidfields['salesperson3'] = 'Salesperson 3';
		}
		return $invalidfields;
	}

	/**
	 * Update Customer's Tax Fields
	 * @param  WireInput $input   Input Data
	 * @param  Customer  $customer
	 * @return array
	 */
	protected function _inputUpdateTaxes(WireInput $input, Customer $customer) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$invalidfields = [];

		$mtm = Codes\Mar\Mtm::getInstance();

		$customer->setTaxcode($values->text('taxcode'));
		$customer->setTaxexemptnbr($values->text('taxexemptnbr', ['maxLength' => $this->fieldAttribute('taxexemptnbr', 'maxlength')]));

		if ($mtm->exists($customer->taxcode) === false) {
			$invalidfields['taxcode']  = 'Tax Code';
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
	Dplus Requests
============================================================= */
	/**
	 * Return Request Data Neeeded for Dplus Update
	 * @param  Record $record
	 * @return array
	 */
	protected function generateRequestData(Record $record) {
		$dplusdb = $this->wire('modules')->get('DplusDatabase')->db_name;
		$table   = static::DPLUS_TABLE;
		return ["DBNAME=$dplusdb", 'UPDATECODETABLE', "TABLE=$table", "CODE=$record->id"];
	}

/* =============================================================
	Record Locker Functions
============================================================= */
	/**
	 * Return Key for Code
	 * @param  Record   $record
	 * @return string
	 */
	public function getRecordlockerKey(Record $record) {
		return implode(FunctionLocker::glue(), [$record->id]);
	}

/* =============================================================
	Supplemental
============================================================= */
	public function getStates() {
		$filter = new Filters\Misc\StateCode();
		return $filter->query->find();
	}

	/**
	 * Return Warehouse Description
	 * @param  string $id  Warehouse ID
	 * @return string
	 */
	public function descriptionWhse($id) {
		return Codes\Min\Iwhm::getInstance()->name($id);
	}

	/**
	 * Return Tax Code Description
	 * @param  string $id  Tax Code ID
	 * @return string
	 */
	public function descriptionTaxcode($id) {
		return Codes\Mar\Mtm::getInstance()->description($id);
	}

	/**
	 * Return Terms Code Description
	 * @param  string $id  Terms Code ID
	 * @return string
	 */
	public function descriptionTermscode($id) {
		return Codes\Mar\Trm::getInstance()->description($id);
	}

	/**
	 * Return Terms Code Description
	 * @param  string $id  SalesPerson ID
	 * @return string
	 */
	public function descriptionSalesperson($id) {
		return Codes\Mar\Spm::getInstance()->name($id);
	}

	/**
	 * Return Ship Via Code Description
	 * @param  string $id  Ship Via ID
	 * @return string
	 */
	public function descriptionShipvia($id) {
		return Codes\Mar\Csv::getInstance()->description($id);
	}

	/**
	 * Return Price Code Description
	 * @param  string $id  Price Code ID
	 * @return string
	 */
	public function descriptionPricecode($id) {
		return Codes\Mar\Cpm::getInstance()->description($id);
	}

	/**
	 * Return Commission Code Description
	 * @param  string $id  Commission Code ID
	 * @return string
	 */
	public function descriptionCommcode($id) {
		return Codes\Mar\Ccm::getInstance()->description($id);
	}
}
