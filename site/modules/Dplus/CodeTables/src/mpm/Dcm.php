<?php namespace Dplus\Codes\Mpm;
// Purl URI Library
use Purl\Url;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Model;
// Dplus Models
use PrWorkCenterQuery, PrWorkCenter;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Codes
use Dplus\Codes\Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the CNFM code table
 */
class Dcm extends Base {
	const MODEL              = 'PrWorkCenter';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'po_confirm_code';
	const DESCRIPTION        = 'Work Center Code';
	const DESCRIPTION_RECORD = 'Work Center Code';
	const RESPONSE_TEMPLATE  = 'Work Center Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'dcm';
	const DPLUS_TABLE           = 'DCM';

	protected static $instance;

	/**
	 * Return the Max Length of characters for the code
	 * NOTE: Used for the JS
	 * @return int
	 */
	public function codeMaxLength() {
		return PrWorkCenter::CODELENGTH;
	}

	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => 4],
		'description' => ['type' => 'text', 'maxlength' => 20],
	];

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
		if (array_key_exists($field, self::FIELD_ATTRIBUTES) === false) {
			return false;
		}
		if (array_key_exists($attr, self::FIELD_ATTRIBUTES[$field]) === false) {
			return false;
		}
		return self::FIELD_ATTRIBUTES[$field][$attr];
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
		$q->select(PrWorkCenter::get_aliasproperty('id'));
		return $q->find()->toArray();
	}

	/**
	 * Return the Code records from Database
	 * @return ObjectCollection
	 */
	public function codes() {
		$q = $this->getQueryClass();
		return $q->find();
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @return PrWorkCenter
	 */
	public function new($id = '') {
		$code = new PrWorkCenter();
		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		return $code;
	}
}
