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
	const DESCRIPTION_RECORD = 'alesperson Group Code';
	const RESPONSE_TEMPLATE  = 'alesperson Group Code{code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'spgpm';
	const DPLUS_TABLE           = 'SPGPM';
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
		$q->select(SalespersonGroupCode::aliasproperty('id'));
		return $q->find()->toArray();
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @return SalespersonGroupCode
	 */
	public function new($id = '') {
		$code = new SalespersonGroupCode();
		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->string($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		return $code;
	}
}
