<?php namespace Dplus\Codes\Mar;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use ShipviaQuery, Shipvia;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes\Base\Simple as Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the CSV code table
 * TODO: FINISH
 */
class Csv extends Base {
	const MODEL              = 'Shipvia';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'ar_cust_svia';
	const DESCRIPTION        = 'Ship Via Code';
	const DESCRIPTION_RECORD = 'Ship Via Code';
	const RESPONSE_TEMPLATE  = 'Ship Via Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'csv';
	const DPLUS_TABLE           = 'CSV';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => Shipvia::MAX_LENGTH_CODE],
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
