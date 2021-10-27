<?php namespace Dplus\Codes\Mpm;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use PrResourceQuery, PrResource;
// Dplus Codes
use Dplus\Codes\Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the RCM code table
 */
class Rcm extends Base {
	const MODEL              = 'PrResource';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'pr_resource_code';
	const DESCRIPTION        = 'Resource Code';
	const DESCRIPTION_RECORD = 'Resource Code';
	const RESPONSE_TEMPLATE  = 'Resource Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'rcm';
	const DPLUS_TABLE           = 'RCM';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => PrResource::CODELENGTH],
		'description' => ['type' => 'text', 'maxlength' => 20],
	];

	protected static $instance;

	/**
	 * Return Array ready for JSON
	 * @param  Code  $code Code
	 * @return array
	 */
	public function codeJson(Code $code) {
		return ['code' => $code->code, 'description' => $code->description, 'workcenterid' => $code->workcenterid];
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
		$q->select(PrResource::aliasproperty('id'));
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
	 * @return PrResource
	 */
	public function new($id = '') {
		$code = new PrResource();
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
		if ($values->text('workcenterid') != '' && $this->exists($values->text('workcenterid')) === false) {
			$invalidfields['workcenterid'] = "Work Center";
			return $invalidfields;
		}
		$code->setWorkcenterid($values->text('workcenterid'));
		return $invalidfields;
	}
}
