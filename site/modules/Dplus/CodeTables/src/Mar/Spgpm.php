<?php namespace Dplus\Codes\Mar;
// Dplus Models
use SalespersonGroupCode;
// Dplus Codes
use Dplus\Codes\AbstractCodeTableEditableSingleKey;

/**
 * Class that handles the CRUD of the SPGPM code table
 */
class Spgpm extends AbstractCodeTableEditableSingleKey {
	const MODEL              = 'SalespersonGroupCode';
	const MODEL_KEY          = 'code';
	const MODEL_TABLE        = 'ar_cust_spgp';
	const DESCRIPTION        = 'Salesperson Group Code';
	const DESCRIPTION_RECORD = 'Salesperson Group Code';
	const RESPONSE_TEMPLATE  = 'Salesperson Group Code{code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'spgpm';
	const DPLUS_TABLE           = 'SPGPM';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => 6],
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
		$q->select(SalespersonGroupCode::aliasproperty('id'));
		return $q->find()->toArray();
	}
}
