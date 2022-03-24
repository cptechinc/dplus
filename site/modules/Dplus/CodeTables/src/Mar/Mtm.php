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
 * Class that handles the CRUD of the CUC code table
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
		$q->select(ArTaxCode::aliasproperty('id'));
		return $q->find()->toArray();
	}

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