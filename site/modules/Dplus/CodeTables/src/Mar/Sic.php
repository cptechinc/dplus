<?php namespace Dplus\Codes\Mar;
// Propel Classes
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// Dplus Models
use ArStandardIndustrialClass;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes;
use Dplus\Codes\AbstractCodeTableEditableSingleKey;

/**
 * Class that handles the CRUD of the SIC code table
 */
class Sic extends AbstractCodeTableEditableSingleKey {
	const MODEL              = 'ArStandardIndustrialClass';
	const MODEL_KEY          = 'code';
	const MODEL_TABLE        = 'ar_cust_sic';
	const DESCRIPTION        = 'Standard Industrial Class';
	const DESCRIPTION_RECORD = 'Standard Industrial Class';
	const RESPONSE_TEMPLATE  = 'Standard Industrial Class {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'sic';
	const DPLUS_TABLE           = 'SIC';
	const FIELD_ATTRIBUTES = [
		'code'             => ['type' => 'text', 'maxlength' => ArStandardIndustrialClass::MAX_LENGTH_CODE],
		'description'      => ['type' => 'text', 'maxlength' => 40],
	];

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return the IDs for the Work Center Confirm Code
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
	 * Return new ArStandardIndustrialClass
	 * @param  string $id Code
	 * @return ArStandardIndustrialClass
	 */
	public function new($id = '') {
		$code = new ArStandardIndustrialClass();

		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->string($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		return $code;
	}
}
