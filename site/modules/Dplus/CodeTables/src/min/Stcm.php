<?php namespace Dplus\Codes\Min;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use InvStockCodeQuery, InvStockCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes\AbstractCodeTableEditableSingleKey;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the STCM code table
 */
class Stcm extends AbstractCodeTableEditableSingleKey {
	const MODEL              = 'InvStockCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'inv_stcm_code';
	const DESCRIPTION        = 'Inventory Stock Code';
	const DESCRIPTION_RECORD = 'Inventory Stock Code';
	const RESPONSE_TEMPLATE  = 'Inventory Stock Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'stcm';
	const DPLUS_TABLE           = 'STCM';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => InvStockCode::MAX_LENGTH_CODE],
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
		$q->select(InvStockCode::aliasproperty('id'));
		return $q->find()->toArray();
	}
}
