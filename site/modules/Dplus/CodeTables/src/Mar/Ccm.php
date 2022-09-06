<?php namespace Dplus\Codes\Mar;
// Dplus Models
use ArCommissionCode;
// Dplus Codes
use Dplus\Codes\AbstractCodeTableEditable;
use Dplus\Codes\SingleKeyTraits;
use Dplus\Codes\SingleKeyEditableTraits;

/**
 * Class that handles the CRUD of the CCM code table
 */
class Ccm extends AbstractCodeTableEditable {
	use SingleKeyTraits;
	use SingleKeyEditableTraits;

	const MODEL              = 'ArCommissionCode';
	const MODEL_KEY          = 'code';
	const MODEL_TABLE        = 'ar_cust_comm';
	const DESCRIPTION        = 'Customer Commission Code';
	const DESCRIPTION_RECORD = 'Customer Commission Code';
	const RESPONSE_TEMPLATE  = 'Customer Commission Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'ccm';
	const DPLUS_TABLE           = 'CCM';
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
		$q->select(ArCommissionCode::aliasproperty('id'));
		return $q->find()->toArray();
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @return ArCommissionCode
	 */
	public function new($id = '') {
		$code = new ArCommissionCode();
		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		return $code;
	}
}
