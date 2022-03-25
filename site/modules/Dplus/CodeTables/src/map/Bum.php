<?php namespace Dplus\Codes\Map;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use ApBuyerQuery, ApBuyer;
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
class Bum extends Base {
	const MODEL              = 'ApBuyer';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'ap_buyr_code';
	const DESCRIPTION        = 'Vendor Buyer Code';
	const DESCRIPTION_RECORD = 'Vendor Buyer Code';
	const RESPONSE_TEMPLATE  = 'Vendor Buyer Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'bum';
	const DPLUS_TABLE           = 'BUM';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => 6],
		'description' => ['type' => 'text', 'maxlength' => 20],
	];

	/** @var self */
	protected static $instance;

	private $fieldAttributes;

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return all IDs
	 * @return array
	 */
	public function ids() {
		$q = $this->query();
		$q->select(ApBuyer::aliasproperty('id'));
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
			'email'       => $code->email,
		];
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @return ApBuyer
	 */
	public function new($id = '') {
		$code = new ApBuyer();
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
		$code->setEmail($values->email('email'));
		return $invalidfields;
	}
}
