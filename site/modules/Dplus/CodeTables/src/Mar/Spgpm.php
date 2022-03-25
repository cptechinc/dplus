<?php namespace Dplus\Codes\Mar;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use SalespersonGroupCodeQuery, SalespersonGroupCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes\Base\Simple as Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the SPGPM code table
 */
class Spgpm extends Base {
	const MODEL              = 'SalespersonGroupCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'ar_cust_spgp';
	const DESCRIPTION        = 'Salesperson Group Code';
	const DESCRIPTION_RECORD = 'Salesperson Group Code';
	const RESPONSE_TEMPLATE  = 'Salesperson Group Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'spgpm';
	const DPLUS_TABLE           = 'SPGPM';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => SalespersonGroupCode::MAX_LENGTH_CODE],
		'description' => ['type' => 'text', 'maxlength' => 20],
	];

	/** @var self */
	protected static $instance;

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return all IDs
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
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		return $code;
	}
}
