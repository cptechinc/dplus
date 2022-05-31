<?php namespace Dplus\Codes\Min;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use CustomerStockingCellQuery, CustomerStockingCell;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes\Base\Simple as Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the CSCCM code table
 */
class Csccm extends Base {
	const MODEL              = 'CustomerStockingCell';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'inv_cell_code';
	const DESCRIPTION        = 'Customer Stocking Cell';
	const DESCRIPTION_RECORD = 'Customer Stocking Cell';
	const RESPONSE_TEMPLATE  = 'Customer Stocking Cell {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'csccm';
	const DPLUS_TABLE           = 'CSCCM';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => CustomerStockingCell::MAX_LENGTH_CODE],
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
