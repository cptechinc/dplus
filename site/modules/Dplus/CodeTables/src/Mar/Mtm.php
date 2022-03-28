<?php namespace Dplus\Codes\Mar;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use ArTaxCodeQuery, ArTaxCode;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes\Base\Simple as Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the MTM code table
 */
class Mtm extends Base {
	const MODEL              = 'ArTaxCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'ar_cust_mtax';
	const DESCRIPTION        = 'Customer Tax Code';
	const DESCRIPTION_RECORD = 'Customer Tax Code';
	const RESPONSE_TEMPLATE  = 'Customer Tax Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'mtm';
	const DPLUS_TABLE           = 'MTM';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => ArTaxCode::MAX_LENGTH_CODE],
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
	 * @return ArTaxCode
	 */
	public function new($id = '') {
		$code = new ArTaxCode();
		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		return $code;
	}
}
