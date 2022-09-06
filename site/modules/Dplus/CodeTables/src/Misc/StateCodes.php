<?php namespace Dplus\Codes\Misc;
// Propel Classes
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
// use ProcessWire\WireInput;
// Dplus Models
use StateCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes;
use Dplus\Codes\AbstractCodeTable;
use Dplus\Codes\SingleKeyTraits;

/**
 * Class that handles validation of StateCodes
 * NOTE: table is readonly
 */
class StateCodes extends AbstractCodeTable {
	use SingleKeyTraits;

	const MODEL              = 'StateCode';
	const MODEL_KEY          = 'code';
	const MODEL_TABLE        = 'state_codes';
	const DESCRIPTION        = 'State Code';
	const DESCRIPTION_RECORD = 'State Code';
	const RESPONSE_TEMPLATE  = 'State Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'statecd';
	const DPLUS_TABLE           = 'STATECD';
	const FIELD_ATTRIBUTES = [
		'code'             => ['type' => 'text'],
		'description'      => ['type' => 'text'],
		'abbreviation'     => ['type' => 'text'],
		'name'             => ['type' => 'text'],
	];

	public function codeJson(Code $code) {
		$json = parent::codeJson($code);
		foreach (array_keys(self::FIELD_ATTRIBUTES) as $field) {
			$json[$field] = $code->$field;
		}
		return $json;
	}

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return IDs
	 * @return array
	 */
	public function ids() {
		$q = $this->query();
		$q->select(StateCode::aliasproperty('id'));
		return $q->find()->toArray();
	}
}
