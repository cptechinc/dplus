<?php namespace Dplus\Codes\Mar;
// Dplus Models
use ArTermsCode;
// Dplus Codes
use Dplus\Codes\AbstractCodeTableEditableSingleKey;

/**
 * Class that handles the CRUD of the TRM code table
 */
class Trm extends AbstractCodeTableEditableSingleKey {
	const MODEL              = 'ArTermsCode';
	const MODEL_KEY          = 'code';
	const MODEL_TABLE        = 'ar_term_code';
	const DESCRIPTION        = 'Customer Terms Code';
	const DESCRIPTION_RECORD = 'Customer Terms Code';
	const RESPONSE_TEMPLATE  = 'Customer Terms Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'trm';
	const DPLUS_TABLE           = 'TRM';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => 4],
		'description' => ['type' => 'text', 'maxlength' => 20],
	];

	protected static $instance;

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return the IDs for the Purchase Order Confirm Code
	 * @return array
	 */
	public function ids() {
		$q = $this->query();
		$q->select(ArTermsCode::aliasproperty('id'));
		return $q->find()->toArray();
	}
}
