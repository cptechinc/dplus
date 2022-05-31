<?php namespace Dplus\Codes\Mgl;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use GlCodeQuery, GlCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Codes
use Dplus\Codes\Base\Simple as Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the MHM (GL MASTER) code table
 */
class Mhm extends Base {
	const MODEL              = 'GlCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'gl_master';
	const DESCRIPTION        = 'General Ledger Code';
	const DESCRIPTION_RECORD = 'General Ledger Code';
	const RESPONSE_TEMPLATE  = 'General Ledger Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'text';
	const DPLUS_TABLE           = 'DTM';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => 6],
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

/* =============================================================
	CRUD Processing
============================================================= */
}
