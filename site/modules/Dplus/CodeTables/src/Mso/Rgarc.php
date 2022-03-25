<?php namespace Dplus\Codes\Mso;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use SoReasonCodeQuery, SoReasonCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes\Base\Simple as Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the RGARC code table
 */
class Rgarc extends Base {
	const MODEL              = 'SoReasonCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'so_reas_code';
	const DESCRIPTION        = 'RGA/Return Reason Code';
	const DESCRIPTION_RECORD = 'RGA/Return Reason Code';
	const RESPONSE_TEMPLATE  = 'RGA/Return Reason Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'rgarc';
	const DPLUS_TABLE           = 'RGARC';
	const FIELD_ATTRIBUTES = [
		'code'         => ['type' => 'text', 'maxlength' => SoReasonCode::MAX_LENGTH_CODE],
		'description'  => ['type' => 'text', 'maxlength' => 30],
	];

	/** @var self */
	protected static $instance;

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return the IDs for the Work Center Confirm Code
	 * @return array
	 */
	public function ids() {
		$q = $this->query();
		$q->select(SoReasonCode::aliasproperty('id'));
		return $q->find()->toArray();
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @return SoReasonCode
	 */
	public function new($id = '') {
		$code = new SoReasonCode();
		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		return $code;
	}

}
