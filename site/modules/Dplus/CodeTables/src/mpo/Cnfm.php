<?php namespace Dplus\Codes\Mpo;
// Purl URI Library
use Purl\Url;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Model;
// Dplus Models
use PoConfirmCodeQuery, PoConfirmCode;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Codes
use Dplus\Codes\AbstractCodeTableEditableSingleKey;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the CNFM code table
 */
class Cnfm extends AbstractCodeTableEditableSingleKey {
	const MODEL              = 'PoConfirmCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'po_confirm_code';
	const DESCRIPTION        = 'PO Confirmation Code';
	const DESCRIPTION_RECORD = 'PO Confirmation Code';
	const RESPONSE_TEMPLATE  = 'PO Confirmation Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'cnfm';
	const DPLUS_TABLE           = 'CNFM';
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
		$q->select(PoConfirmCode::aliasproperty('id'));
		return $q->find()->toArray();
	}
}
