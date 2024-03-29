<?php namespace Dplus\Codes\Min;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use InvPriceCodeQuery, InvPriceCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes\AbstractCodeTableEditableSingleKey;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the IGPM code table
 */
class Igpm extends AbstractCodeTableEditableSingleKey {
	const MODEL              = 'InvPriceCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'inv_pric_code';
	const DESCRIPTION        = 'Inventory Price Code';
	const DESCRIPTION_RECORD = 'Inventory Price Code';
	const RESPONSE_TEMPLATE  = 'Inventory Price Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'igpm';
	const DPLUS_TABLE           = 'IGPM';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => InvPriceCode::MAX_LENGTH_CODE],
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
		$q->select(InvPriceCode::aliasproperty('id'));
		return $q->find()->toArray();
	}
}
