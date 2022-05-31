<?php namespace Dplus\Codes\Mar;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use ArStandardIndustrialClassQuery, ArStandardIndustrialClass;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes\Base\Simple as Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the SIC code table
 */
class Sic extends Base {
	const MODEL              = 'ArStandardIndustrialClass';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'ar_cust_sic';
	const DESCRIPTION        = 'Standard Industrial Class';
	const DESCRIPTION_RECORD = 'Standard Industrial Class';
	const RESPONSE_TEMPLATE  = 'Standard Industrial Class {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'sic';
	const DPLUS_TABLE           = 'SIC';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => ArStandardIndustrialClass::MAX_LENGTH_CODE],
		'description' => ['type' => 'text', 'maxlength' => 36],
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
