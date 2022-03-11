<?php namespace Dplus\Codes\Mar;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use ShiptoUserCodeQuery, ShiptoUserCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes\Base\Simple as Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the SUC code table
 */
class Suc extends Base {
	const MODEL              = 'ShiptoUserCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'ar_cust_susr';
	const DESCRIPTION        = 'Ship-To User Code';
	const DESCRIPTION_RECORD = 'Ship-To User Code';
	const RESPONSE_TEMPLATE  = 'Ship-To User Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'suc';
	const DPLUS_TABLE           = 'SUC';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => ShiptoUserCode::MAX_LENGTH_CODE],
		'description' => ['type' => 'text', 'maxlength' => 20],
	];


/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return the IDs for the Work Center Confirm Code
	 * @return array
	 */
	public function ids() {
		$q = $this->query();
		$q->select(ShiptoUserCode::aliasproperty('id'));
		return $q->find()->toArray();
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @return ShiptoUserCode
	 */
	public function new($id = '') {
		$code = new ShiptoUserCode();
		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		return $code;
	}
}
