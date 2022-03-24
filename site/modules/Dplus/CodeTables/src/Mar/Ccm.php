<?php namespace Dplus\Codes\Mar;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use ArCommissionCodeQuery, ArCommissionCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes\Base\Simple as Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the CCM code table
 */
class Ccm extends Base {
	const MODEL              = 'ArCommissionCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'ar_cust_comm';
	const DESCRIPTION        = 'Customer Commission Code';
	const DESCRIPTION_RECORD = 'Customer Commission Code';
	const RESPONSE_TEMPLATE  = 'Customer Commission Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'ccm';
	const DPLUS_TABLE           = 'CCM';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => ArCommissionCode::MAX_LENGTH_CODE],
		'description' => ['type' => 'text', 'maxlength' => 20],
	];
	
	protected static $instance;


/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return the IDs for the Work Center Confirm Code
	 * @return array
	 */
	public function ids() {
		$q = $this->query();
		$q->select(ArCommissionCode::aliasproperty('id'));
		return $q->find()->toArray();
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @return ArCommissionCode
	 */
	public function new($id = '') {
		$code = new ArCommissionCode();
		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		return $code;
	}
}