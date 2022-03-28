<?php namespace Dplus\Codes\Mar;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use ArRouteCodeQuery, ArRouteCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes\Base\Simple as Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the CRTM code table
 */
class Crtm extends Base {
	const MODEL              = 'ArRouteCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'ar_cust_rout';
	const DESCRIPTION        = 'Customer Route Code';
	const DESCRIPTION_RECORD = 'Customer Route Code';
	const RESPONSE_TEMPLATE  = 'Customer Route Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'crtm';
	const DPLUS_TABLE           = 'CRTM';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => ArRouteCode::MAX_LENGTH_CODE],
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
	/**
	 * Return New Code
	 * @return ArRouteCode
	 */
	public function new($id = '') {
		$code = new ArRouteCode();
		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		return $code;
	}
}
