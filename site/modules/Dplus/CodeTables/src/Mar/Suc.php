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

	/** @var self */
	protected static $instance;


/* =============================================================
	CRUD Read, Validate Functions
============================================================= */

/* =============================================================
	CRUD Creates
============================================================= */
}
