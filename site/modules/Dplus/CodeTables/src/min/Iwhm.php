<?php namespace Dplus\Codes\Min;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use WarehouseQuery, Warehouse;
// Dpluso Models
use StatesQuery, States;
use CountryQuery, Country;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes;
use Dplus\Codes\AbstractCodeTableEditableSingleKey;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the IWHM code table
 *
 * @property Iwhm\Qnotes $qnotes
 */
class Iwhm extends AbstractCodeTableEditableSingleKey {
	const MODEL              = 'Warehouse';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'inv_whse_code';
	const DESCRIPTION        = 'Warehouse';
	const DESCRIPTION_RECORD = 'Warehouse';
	const RESPONSE_TEMPLATE  = 'Warehouse {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'iwhm';
	const DPLUS_TABLE           = 'IWHM';
	const FIELD_ATTRIBUTES = [
		'code' => ['type' => 'text', 'maxlength' => Warehouse::MAX_LENGTH_CODE],
		'name' => ['type' => 'text', 'maxlength' => 30],
		'address'   => ['type' => 'text', 'maxlength' => 30],
		'address2'  => ['type' => 'text', 'maxlength' => 30],
		'city'      => ['type' => 'text', 'maxlength' => 16],
		'zip'       => ['type' => 'text', 'maxlength' => 10],
		'extension' => ['type' => 'text', 'maxlength' => 7],
		'qcbin'    => ['type' => 'text', 'maxlength' => 8],
		'productionbin'   => ['type' => 'text', 'maxlength' => 8, 'disabled' => true],
		'binarrangement'  => ['type' => 'text', 'default' => 'L', 'options' => ['L' => 'List', 'R' => 'Range']],
		'pickdetail'      => ['type' => 'text', 'default' => 'N', 'options' => ['A' => 'Available', 'S' => 'Selected', 'N' => 'No']],
		'consignment'     => ['type' => 'text', 'default' => 'N'],
	];
	const FILTERABLE_FIELDS = ['code', 'name'];

	protected static $instance;
	protected $fieldAttributes;

	public function __construct() {
		parent::__construct();
		$this->qnotes = Iwhm\Qnotes::instance();
	}

/* =============================================================
	Field Configs
============================================================= */

	public function initFieldAttributes() {
		$configIn = Configs\In::config();
		$custID   = Configs\Sys::custid();

		$attributes = self::FIELD_ATTRIBUTES;
		$attributes['productionbin']['disabled'] = $configIn->useControlbin() === false && $custID != 'ALUMAC';
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

		if (empty($this->fieldAttributes)) {
			$this->initFieldAttributes();
		}

		if (array_key_exists($field, $this->fieldAttributes) === false) {
			return false;
		}
		if (array_key_exists($attr, $this->fieldAttributes[$field]) === false) {
			return false;
		}
		return $this->fieldAttributes[$field][$attr];
	}

	/**
	 * Return Array ready for JSON
	 * @param  Code  $code Code
	 * @return array
	 */
	public function codeJson(Code $code) {
		return [
			'id'   => $code->id,
			'name' => $code->name
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
		$q->select(Warehouse::aliasproperty('id'));
		return $q->find()->toArray();
	}

	/**
	 * Return Warehouse
	 * @param  string $id Warehouse ID
	 * @return Warehouse
	 */
	public function whse($id) {
		return $this->code($id);
	}

	/**
	 * Return Warehouse Name
	 * @param  string $id Warehouse ID
	 * @return string
	 */
	public function name($id) {
		if ($this->exists($id) === false) {
			return '';
		}
		$model = static::modelClassName();
		$q = $this->queryId($id);
		$q->select($model::aliasproperty('name'));
		return $q->findOne();
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @return Warehouse
	 */
	public function new($id = '') {
		/** @var Warehouse */
		$code = parent::new($id);
		$code->setPickdetail($this->fieldAttribute('pickdetail', 'default'));
		$code->setConsignment($this->fieldAttribute('consignment', 'default'));
		$code->setBinarrangement($this->fieldAttribute('binarrangement', 'default'));
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
		$invalidfields = parent::_inputUpdate($input, $code);
		$invalidfieldsIwhm = $this->_inputUpdateIwhm($input, $code);
		$invalidfields = array_merge($invalidfields, $invalidfieldsIwhm);
		return $invalidfields;
	}

	/**
	 * Update Warehouse IWHM fields
	 * @param  WireInput $input     Input Data
	 * @param  Warehouse $warehouse
	 * @return array
	 */
	private function _inputUpdateIwhm(WireInput $input, Warehouse $warehouse) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$warehouse->setName($values->text('name', ['maxLength' => $this->fieldAttribute('name', 'maxlength')]));
		$invalidfields = [
			'address' => $this->_inputUpdateWhseAddress($input, $warehouse),
			'contact' => $this->_inputUpdateWhseContact($input, $warehouse),
			'setup'   => $this->_inputUpdateWhseSetup($input, $warehouse),
			'whses'   => $this->_inputUpdateWhseWarehouses($input, $warehouse),
		];
		return array_merge($invalidfields['address'], $invalidfields['contact'], $invalidfields['setup'], $invalidfields['whses']);
	}

	/**
	 * Update Warehouse Address fields
	 * @param  WireInput $input     Input Data
	 * @param  Warehouse $warehouse
	 * @return array
	 */
	private function _inputUpdateWhseAddress(WireInput $input, Warehouse $warehouse) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$warehouse->setName($values->text('name', ['maxLength' => $this->fieldAttribute('name', 'maxlength')]));
		$warehouse->setAddress($values->text('address', ['maxLength' => $this->fieldAttribute('address', 'maxlength')]));
		$warehouse->setAddress2($values->text('address2', ['maxLength' => $this->fieldAttribute('address2', 'maxlength')]));
		$warehouse->setCity($values->text('city', ['maxLength' => $this->fieldAttribute('city', 'maxlength')]));
		$warehouse->setState($values->text('state', ['maxLength' => 2]));
		$warehouse->setZip($values->text('zip', ['maxLength' => $this->fieldAttribute('zip', 'maxlength')]));
		return [];
	}

	/**
	 * Update Warehouse Contact fields
	 * @param  WireInput $input     Input Data
	 * @param  Warehouse $warehouse
	 * @return array
	 */
	private function _inputUpdateWhseContact(WireInput $input, Warehouse $warehouse) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$warehouse->setExtension($values->text('extension', ['maxLength' => $this->fieldAttribute('extension', 'maxlength')]));
		$warehouse->setEmail($values->email('email'));

		$phone_arr = explode('-', $values->text('phone'));
		$warehouse->setPhone_area($phone_arr[0]);
		$warehouse->setPhone_prefix($phone_arr[1]);
		$warehouse->setPhone_line($phone_arr[2]);

		$fax_arr = explode('-', $values->text('fax'));
		$warehouse->setFax_area($fax_arr[0]);
		$warehouse->setFax_prefix($fax_arr[1]);
		$warehouse->setFax_line($fax_arr[2]);
		return [];
	}

	/**
	 * Update Warehouse Setup fields
	 * @param  WireInput $input     Input Data
	 * @param  Warehouse $warehouse
	 * @return array
	 */
	private function _inputUpdateWhseSetup(WireInput $input, Warehouse $warehouse) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$invalidfields = [];

		// Pick Detail
		$pickdetail = in_array($values->text('pickdetail'), array_keys($this->fieldAttribute('pickdetail', 'options'))) ? $values->text('pickdetail') : $this->fieldAttribute('pickdetail', 'default');
		$warehouse->setPickdetail($pickdetail);
		// Bin Arrangement
		$binarrangement = in_array($values->text('binarrangement'), array_keys($this->fieldAttribute('binarrangement', 'options'))) ? $values->text('binarrangement') : $this->fieldAttribute('binarrangement', 'default');
		$warehouse->setBinarrangement($binarrangement);

		$warehouse->setConsignment($values->yn('consignment'));
		$warehouse->setQcbin($values->text('qcbin', ['maxLength' => $this->fieldAttribute('qcbin', 'maxlength')]));
		$warehouse->setProductionbin($values->text('productionbin', ['maxLength' => $this->fieldAttribute('productionbin', 'maxlength')]));

		if ($this->fieldAttribute('productionbin', 'disabled')) {
			$warehouse->setProductionbin('');
		}

		if (Codes\Mar\Cmm::getInstance()->exists($values->string('custid')) === false) {
			$invalidfields['custid'] = 'Cash Customer';
		} else {
			$warehouse->setCustid($values->string('custid'));
		}

		return $invalidfields;
	}

	/**
	 * Set Warehouses Fields
	 * @param  WireInputData $input
	 * @param  Warehouse     $warehouse
	 * @return array
	 */
	private function _inputUpdateWhseWarehouses(WireInput $input, Warehouse $warehouse) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$warehouse->setWhseprofit($warehouse->id);
		$warehouse->setWhseasset($warehouse->id);
		$warehouse->setWhsesupply($warehouse->id);

		$fields = ['whseprofit', 'whseasset', 'whsesupply'];

		foreach ($fields as $field) {
			$setField = 'set'.ucfirst($field);
			$warehouse->$setField($warehouse->id);

			$id = $values->string($field);

			if ($id == $warehouse->id) {
				continue;
			}

			if ($this->exists($id)) {
				$warehouse->$setField($id);
			}
		}
		return [];
	}

/* =============================================================
	Supplemental
============================================================= */
	/**
	 * Return States
	 * @return States[]|ObjectCollection
	 */
	public function getStates() {
		return StatesQuery::create()->find();
	}

	/**
	 * Return Countries
	 * @return Country[]|ObjectCollection
	 */
	public function getCountries() {
		return CountryQuery::create()->find();
	}
}
