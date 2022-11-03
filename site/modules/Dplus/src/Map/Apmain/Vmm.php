<?php namespace Dplus\Map\Apmain;
// Propel Classes
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Record;
// ProcessWire
use ProcessWire\WireInput;
// Dplus Models
use VendorQuery, Vendor;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as FunctionLocker;
// Dplus Filters
use Dplus\Filters;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes;
// Dplus Crud
use Dplus\Crud\AbstractManager;
use Dplus\Crud\Response;


/**
 * Class that handles the CRUD of the Vendor Table
 *
 * @property array  $attributes Field Attributes, some defaults are loaded from configs
 */
class Vmm extends AbstractManager {
	const MODEL              = 'Vendor';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'ap_vend_mast';
	const DESCRIPTION        = 'Vendor';
	const DESCRIPTION_RECORD = 'Vendor';
	const RESPONSE_TEMPLATE  = 'Vendor {key} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'vmm';
	const DPLUS_TABLE           = 'VMM';
	// TODO: 
	const FIELD_ATTRIBUTES = [
		'id'       => ['type' => 'text', 'maxlength' => 6],
		'name'     => ['type' => 'text', 'maxlength' => 30],
		'address1' => ['type' => 'text', 'maxlength' => 30],
		'address2' => ['type' => 'text', 'maxlength' => 30],
		'address3' => ['type' => 'text', 'maxlength' => 30],
		'city'     => ['type' => 'text', 'maxlength' => 16],
		'state'    => ['type' => 'text', 'maxlength' => 2],
	];

	protected static $instance;
	private $fieldAttributes;

/* =============================================================
	Field Configs
	TODO: 
============================================================= */

	public function initFieldAttributes() {
		if (empty($this->fieldAttributes) === false) {
			return true;
		}
		$configAR = Configs\Ar::config();

		$attributes = self::FIELD_ATTRIBUTES;
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
	 * Return Query Filtered by Vendor ID
	 * @param  string $id Vendor ID
	 * @return VendorQuery
	 */
	public function queryId($id) {
		return $this->query()->filterById($id);
	}

	/**
	 * Return Query Filtered by Record Number
	 * @param  string $rid  Record Number
	 * @return VendorQuery
	 */
	public function queryRid($id) {
		return $this->query()->filterByRid($id);
	}

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return if Vendor Exists
	 * @param  string $id Vendor ID
	 * @return bool
	 */
	public function exists($id) {
		return boolval($this->queryId($id)->count());
	}

	/**
	 * Return if Vendor Exists By Record Number
	 * @param  string $rid  Record Number
	 * @return bool
	 */
	public function existsByRid($id) {
		return boolval($this->queryRid($id)->count());
	}

	/**
	 * Return the IDs for the Work Center Confirm Code
	 * @return array
	 */
	public function ids() {
		$q = $this->query();
		$q->select(Vendor::aliasproperty('id'));
		return $q->find()->toArray();
	}

	/**
	 * Return Vendor
	 * @param  string $id Vendor ID
	 * @return Vendor
	 */
	public function vendor($id) {
		$q = $this->getQueryClass();
		return $q->findOneById($id);
	}

	/**
	 * Return Vendor
	 * @param  string $id Vendor ID
	 * @return Vendor
	 */
	public function vendorByRid($id) {
		return $this->queryRid($id)->findOne();
	}

	/**
	 * Return Record Position for vendorID
	 * @param  string $id Vendor ID
	 * @return int
	 */
	public function ridByVendorid($id) {
		$q = $this->queryId($id);
		$q->select(Vendor::aliasproperty('rid'));
		return $q->count() ? $q->findOne() : 0;
	}

	/**
	 * Return vendorID by Rid
	 * @param  string $rid Record ID
	 * @return int
	 */
	public function vendoridByRid($rid) {
		$q = $this->queryRid($rid);
		$q->select(Vendor::aliasproperty('vendorid'));
		return $q->count() ? $q->findOne() : '';
	}

	/**
	 * Return Vendor
	 * @param  string $id Vendor ID
	 * @return Vendor
	 */
	public function record($id) {
		return $this->vendor($id);
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
	 * Return New or Existing Vendor
	 * @param  string $id Vendor ID
	 * @return Vendor
	 */
	public function getOrCreate($id) {
		if ($this->exists($id) === false) {
			return $this->new($id);
		}
		return $this->vendor($id);
	}

/* =============================================================
	CRUD Creates
	TODO: 
============================================================= */
	/**
	 * Return New Code
	 * @return Vendor
	 */
	public function new($id = '') {
		$this->initFieldAttributes();

		$vendor = new Vendor();
		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('id', 'maxlength')]);
			$vendor->setId($id);
		}

		// Set Default Values
		foreach ($this->fieldAttributes as $name => $attr) {
			if (array_key_exists('default', $attr)) {
				$setField = 'set' . ucfirst($name);
				$vendor->$setField($attr['default']);
			}
		}
		return $vendor;
	}

/* =============================================================
	CRUD Processing (UPDATE)
	TODO: 
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


		return [];
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
			$response = Response::responseSuccess("Vendor $id was deleted");
			$response->buildMessage(static::RESPONSE_TEMPLATE);
			$response->setCode($id);
			return true;
		}
		$vendor = $this->vendor($id);
		$record->delete();
		$response = $this->saveAndRespond($vendor);
		$this->setResponse($response);
		return $response->hasSuccess();
	}

/* =============================================================
	Dplus Requests
	TODO: 
============================================================= */
	/**
	 * Return Request Data Neeeded for Dplus Update
	 * @param  Record $record
	 * @return array
	 */
	protected function generateRequestData(Record $record) {
		// $dplusdb = $this->wire('modules')->get('DplusDatabase')->db_name;
		// $table   = static::DPLUS_TABLE;
		// return ["DBNAME=$dplusdb", 'UPDATECODETABLE', "TABLE=$table", "CODE=$record->id"];
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
}
