<?php namespace Dplus\Codes\Min;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use InvAssortmentCodeQuery, InvAssortmentCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes\Base\Simple as Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the IASM code table
 */
class Iasm extends Base {
	const MODEL              = 'InvAssortmentCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'inv_asst_code';
	const DESCRIPTION        = 'Inventory Assortment Code';
	const DESCRIPTION_RECORD = 'Inventory Assortment Code';
	const RESPONSE_TEMPLATE  = 'Inventory Assortment Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'iasm';
	const DPLUS_TABLE           = 'IASM';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => InvAssortmentCode::MAX_LENGTH_CODE],
		'description' => ['type' => 'text', 'maxlength' => 20],
	];

	/** @var self */
	protected static $instance;

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */

/* =============================================================
	CRUD Creates
============================================================= */
}
