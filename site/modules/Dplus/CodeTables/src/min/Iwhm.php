<?php namespace Dplus\Codes\Min;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use WarehouseQuery, Warehouse;
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
	];

	protected static $instance;

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
}
