<?php namespace Dplus\Codes\Map;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use ApTypeCodeQuery, ApTypeCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes\Base\Simple as Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the TTM code table
 * @property array $fieldAttributes Array of Field Attribute data
 */
class Vtm extends Base {
	const MODEL              = 'ApTypeCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'ap_type_code';
	const DESCRIPTION        = 'Vendor Type Code';
	const DESCRIPTION_RECORD = 'Vendor Type Code';
	const RESPONSE_TEMPLATE  = 'Vendor Type Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'vtm';
	const DPLUS_TABLE           = 'VTM';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => 4],
		'description' => ['type' => 'text', 'maxlength' => 20],
		'fabricator'  => ['default' => 'N', 'disabled' => true],
		'production'  => ['default' => 'N', 'disabled' => false],
		'competitor'  => ['default' => 'N', 'disabled' => false],
	];

	/** @var self */
	protected static $instance;

	private $fieldAttributes;

/* =============================================================
	Field Configs
============================================================= */
	/**
	 * Initialize Field Attributes by getting attribute data from configs
	 * @return void
	 */
	public function initFieldAttributes() {
		$configPo = Configs\Po::config();
		$attributes = self::FIELD_ATTRIBUTES;
		$attributes['fabricator']['disabled'] = $configPo->usefabrication() === false;
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

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return the IDs for the Work Center Confirm Code
	 * @return array
	 */
	public function ids() {
		$q = $this->query();
		$q->select(ApTypeCode::aliasproperty('id'));
		return $q->find()->toArray();
	}

	/**
	 * Return Array ready for JSON
	 * @param  Code  $code Code
	 * @return array
	 */
	public function codeJson(Code $code) {
		return [
			'code'        => $code->code,
			'description' => $code->description,
			'fabricator'  => $code->fabricator,
			'production'  => $code->production,
			'competitor'  => $code->competitor
		];
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @return ApTypeCode
	 */
	public function new($id = '') {
		$code = new ApTypeCode();
		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		$code->setFabricator($this->fieldAttribute('fabricator', 'default'));
		$code->setProduction($this->fieldAttribute('production', 'default'));
		$code->setCompetitor($this->fieldAttribute('competitor', 'default'));
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
		$code->setFabricator($this->fieldAttribute('fabricator', 'disabled') ? 'N' : $values->yn('fabricator'));
		$code->setProduction($values->yn('production'));
		$code->setCompetitor($values->yn('competitor'));
		return $invalidfields;
	}
}
