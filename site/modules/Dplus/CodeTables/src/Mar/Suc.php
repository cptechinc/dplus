<?php namespace Dplus\Codes\Mar;
// Propel Classes
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// Dplus Models
use ArShiptoUserCode;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes;
use Dplus\Codes\AbstractCodeTableEditableSingleKey;

/**
 * Class that handles the CRUD of the SUC code table
 */
class Suc extends AbstractCodeTableEditableSingleKey {
	const MODEL              = 'ArShiptoUserCode';
	const MODEL_KEY          = 'code';
	const MODEL_TABLE        = 'ar_cust_susr';
	const DESCRIPTION        = 'Ship-to User Code';
	const DESCRIPTION_RECORD = 'Ship-to User Code';
	const RESPONSE_TEMPLATE  = 'Ship-to User Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'suc';
	const DPLUS_TABLE           = 'SUC';
	const FIELD_ATTRIBUTES = [
		'code'             => ['type' => 'text', 'maxlength' => ArShiptoUserCode::MAX_LENGTH_CODE],
		'description'      => ['type' => 'text', 'maxlength' => 20],
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
		$q->select(ArShiptoUserCode::aliasproperty('id'));
		return $q->find()->toArray();
	}
}
