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
	/**
	 * Return all IDs
	 * @return array
	 */
	public function ids() {
		$q = $this->query();
		$q->select(ArStandardIndustrialClass::aliasproperty('id'));
		return $q->find()->toArray();
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @return ArStandardIndustrialClass
	 */
	public function new($id = '') {
		$code = new ArStandardIndustrialClass();
		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		return $code;
	}
}
