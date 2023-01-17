<?php namespace Dplus\Codes\Mar;
// Dplus Models
use ArTermsGroup;
// Dplus Codes
use Dplus\Codes\AbstractCodeTableEditableSingleKey;


/**
 * Class that handles the CRUD of the TRMG code table
 */
class Trmg extends AbstractCodeTableEditableSingleKey{
	const MODEL              = 'ArTermsGroup';
	const MODEL_KEY          = 'code';
	const MODEL_TABLE        = 'ar_terms_grp';
	const DESCRIPTION        = 'Terms Group Code';
	const DESCRIPTION_RECORD = 'Terms Group Code';
	const RESPONSE_TEMPLATE  = 'Terms Group Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'trmg';
	const DPLUS_TABLE           = 'TRMG';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => 4],
		'description' => ['type' => 'text', 'maxlength' => 30],
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
		$q->select(ArTermsGroup::aliasproperty('id'));
		return $q->find()->toArray();
	}
}
