<?php namespace Dplus\Codes\Min;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use InvGroupCodeQuery, InvGroupCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes;
use Dplus\Codes\Base\Simple as Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the IGM code table
 */
class Igm extends Base {
	const MODEL              = 'InvGroupCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'inv_grup_code';
	const DESCRIPTION        = 'Inventory Group Code';
	const DESCRIPTION_RECORD = 'Inventory Group Code';
	const RESPONSE_TEMPLATE  = 'Inventory Group Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'igm';
	const DPLUS_TABLE           = 'IGM';
	const FIELD_ATTRIBUTES = [
		'code'             => ['type' => 'text', 'maxlength' => InvGroupCode::MAX_LENGTH_CODE],
		'description'      => ['type' => 'text', 'maxlength' => 20],
		'freightgroup'     => ['type' => 'text', 'maxlength' => 2],
		'surchargetype'    => ['type' => 'text', 'options' => ['D' => 'Dollar', 'P' => 'Percent']],
		'surchargeamount'  => ['type' => 'number', 'precision' => 2],
		'surchargepercent' => ['type' => 'number', 'precision' => 3],
		'webgroup'         => ['type' => 'text', 'disabled' => true, 'default' => 'N'],
		'salesprogram'     => ['type' => 'text', 'disabled' => true, 'default' => 'N'],
		'ecommdesc'        => ['type' => 'text', 'disabled' => true],
		'coop'             => ['type' => 'text', 'default' => 'N'],
	];

	private $fieldAttributes;

/* =============================================================
	Field Configs
============================================================= */

	public function initFieldAttributes() {
		$configAr = Configs\Ar::config();
		$configSo = Configs\So::config();
		$custID   = Configs\Sys::custid();

		$attributes = self::FIELD_ATTRIBUTES;
		$attributes['webgroup']['disabled']     = $configAr->isWebGroup() === false || $custID == 'ALUMAC';
		$attributes['salesprogram']['disabled'] = $configSo->isRequestProgram() === false;
		$attributes['ecommdesc']['disabled']    =  $custID != 'LINDST';
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
		$q->select(InvGroupCode::aliasproperty('id'));
		return $q->find()->toArray();
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @return InvGroupCode
	 */
	public function new($id = '') {
		$code = new InvGroupCode();
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
		$invalidfields   = parent::_inputUpdate($input, $code);
		$invalidfieldsGL = $this->inputUpdateGlAccts($input, $code);
		$invalidfields = array_merge($invalidfields, $invalidfieldsGL);
		return $invalidfields;
	}

	/**
	 * Update GL codes for record
	 * @param  WireInput    $input Input Data
	 * @param  InvGroupCode $code
	 * @return array
	 */
	private function inputUpdateGlAccts(WireInput $input, InvGroupCode $code) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$fields = [
			'sales'     => 'Sales GL Account',
			'credits'   => 'Credits GL Account',
			'cogs'      => 'Cost of Goods Sold GL Account',
			'inventory' => 'Inventory GL Account',
			'dropship'  => 'Drop Ship GL Account',
		];
		$invalidfields = [];
		$mhm = Codes\Mgl\Mhm::getInstance();

		foreach ($fields as $name => $description) {
			$exists = $mhm->exists($values->text($name));

			if ($exists === false) {
				$invalidfields[$name] = $description;
				continue;
			}
			$setField = 'set'.ucfirst($name);
			$code->$setField($values->text($name));
		}
		return $invalidfields;
	}

/* =============================================================
	Supplemental
============================================================= */
	/**
	 * Return GL Code Description
	 * @param  string $id GL Code
	 * @return string
	 */
	public function glCodeDescription($id) {
		$mhm = Codes\Mgl\Mhm::getInstance();
		return $mhm->description($id);
	}

	/**
	 * Return Product Line Code Description
	 * @param  string $id Product Line Code
	 * @return string
	 */
	public function productLineCodeDescription($id) {
		$iplm = Codes\Min\Iplm::getInstance();
		return $iplm->description($id);
	}
}
