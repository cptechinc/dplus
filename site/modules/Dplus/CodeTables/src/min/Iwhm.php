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
use Dplus\Codes\Base\Simple as Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the IWHM code table
 */
class Iwhm extends Base {
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

	protected static $instance;
	private $fieldAttributes;

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
		$code = new Warehouse();
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
