<?php namespace Dplus\Codes\Min;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use InvProductLineCodeQuery, InvProductLineCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes\Base\Simple as Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the IPLM code table
 */
class Iplm extends Base {
	const MODEL              = 'InvProductLineCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'inv_plne_code';
	const DESCRIPTION        = 'Inventory Product Line Code';
	const DESCRIPTION_RECORD = 'Inventory Product Line Code';
	const RESPONSE_TEMPLATE  = 'Inventory Product Line Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'iplm';
	const DPLUS_TABLE           = 'IPLM';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => InvProductLineCode::MAX_LENGTH_CODE],
		'description' => ['type' => 'text', 'maxlength' => 30],
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
		$q->select(InvProductLineCode::aliasproperty('id'));
		return $q->find()->toArray();
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @return InvProductLineCode
	 */
	public function new($id = '') {
		$code = new InvProductLineCode();
		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		return $code;
	}
}
