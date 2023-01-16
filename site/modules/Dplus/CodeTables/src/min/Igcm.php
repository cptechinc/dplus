<?php namespace Dplus\Codes\Min;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use InvCommissionCodeQuery, InvCommissionCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes\AbstractCodeTableEditableSingleKey;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the IGCM code table
 */
class Igcm extends AbstractCodeTableEditableSingleKey {
	const MODEL              = 'InvCommissionCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'inv_comm_code';
	const DESCRIPTION        = 'Inventory Commission Code';
	const DESCRIPTION_RECORD = 'Inventory Commission Code';
	const RESPONSE_TEMPLATE  = 'Inventory Commission Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'iasm';
	const DPLUS_TABLE           = 'IGCM';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => InvCommissionCode::MAX_LENGTH_CODE],
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
		$q->select(InvCommissionCode::aliasproperty('id'));
		return $q->find()->toArray();
	}
}
