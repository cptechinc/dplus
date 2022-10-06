<?php namespace Dplus\Codes\Min;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use InvSpecialCodeQuery, InvSpecialCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes\AbstractCodeTableEditableSingleKey;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the SPIT code table
 */
class Spit extends AbstractCodeTableEditableSingleKey {
	const MODEL              = 'InvSpecialCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'inv_spit_code';
	const DESCRIPTION        = 'Special Item Code';
	const DESCRIPTION_RECORD = 'Special Item Code';
	const RESPONSE_TEMPLATE  = 'Special Item Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'spit';
	const DPLUS_TABLE           = 'SPIT';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => InvSpecialCode::MAX_LENGTH_CODE],
		'description' => ['type' => 'text', 'maxlength' => 30],
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
		$q->select(InvSpecialCode::aliasproperty('id'));
		return $q->find()->toArray();
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @return InvSpecialCode
	 */
	public function new($id = '') {
		$code = new InvSpecialCode();
		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		return $code;
	}
}
