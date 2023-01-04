<?php namespace Dplus\Codes\Mpm;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use PrResourceQuery, PrResource;
// Dplus Codes
use Dplus\Codes\AbstractCodeTableEditableSingleKey;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the RCM code table
 */
class Rcm extends AbstractCodeTableEditableSingleKey {
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
		'description' => ['type' => 'text', 'maxlength' => 30],
	];

	protected static $instance;

	/**
	 * Return Array ready for JSON
	 * @param  Code  $code Code
	 * @return array
	 */
	public function codeJson(Code $code) {
		$dcm = Dcm::getInstance();
		return [
			'code'         => $code->code,
			'description'  => $code->description,
			'workcenterid' => $code->workcenterid,
			'workcenter' => [
				'code'        => $code->workcenterid,
				'description' => $dcm->description($code->workcenterid)
			]
		];
	}

	/**
	 * Return DCM
	 * @return Dcm
	 */
	public function getDcm() {
		return Dcm::getInstance();
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
		$dcm = Dcm::getInstance();

		if ($values->text('workcenterid') != '' && $dcm->exists($values->text('workcenterid')) === false) {
			$invalidfields['workcenterid'] = "Work Center";
			return $invalidfields;
		}
		$code->setWorkcenterid($values->text('workcenterid'));
		return $invalidfields;
	}
}
