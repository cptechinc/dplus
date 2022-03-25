<?php namespace Dplus\Codes\Mar;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use ArCustUserCodeQuery, ArCustUserCode;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes\Base\Simple as Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the CUC code table
 */
class Cuc extends Base {
	const MODEL              = 'ArCustUserCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'ar_cust_cusr';
	const DESCRIPTION        = 'Customer User Code';
	const DESCRIPTION_RECORD = 'Customer User Code';
	const RESPONSE_TEMPLATE  = 'Customer User Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'cuc';
	const DPLUS_TABLE           = 'CUC';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => ArCustUserCode::MAX_LENGTH_CODE],
		'description' => ['type' => 'text', 'maxlength' => 20],
	];

	/** @var self */
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
		$q->select(ArCustUserCode::aliasproperty('id'));
		return $q->find()->toArray();
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @return ArCustUserCode
	 */
	public function new($id = '') {
		$code = new ArCustUserCode();
		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		return $code;
	}
}
