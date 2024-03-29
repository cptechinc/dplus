<?php namespace Dplus\Codes\Mar;
// Dplus Models
use ArRouteCode;
// Dplus Codes
use Dplus\Codes\AbstractCodeTableEditableSingleKey;

/**
 * Class that handles the CRUD of the TRM code table
 */
class Crtm extends AbstractCodeTableEditableSingleKey {
	const MODEL              = 'ArRouteCode';
	const MODEL_KEY          = 'code';
	const MODEL_TABLE        = 'ar_cust_rout';
	const DESCRIPTION        = 'Customer Route Code';
	const DESCRIPTION_RECORD = 'Customer Route Code';
	const RESPONSE_TEMPLATE  = 'Customer Route Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'crtm';
	const DPLUS_TABLE           = 'CRTM';
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
		$q->select(ArRouteCode::aliasproperty('id'));
		return $q->find()->toArray();
	}
}
