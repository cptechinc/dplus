<?php namespace Dplus\Codes\Mgl;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use GlCodeQuery, GlCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Codes
use Dplus\Codes\Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the MHM (GL MASTER) code table
 */
class Mhm extends Base {
	const MODEL              = 'GlCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'gl_master';
	const DESCRIPTION        = 'General Ledger Code';
	const DESCRIPTION_RECORD = 'General Ledger Code';
	const RESPONSE_TEMPLATE  = 'General Ledger Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'text';
	const DPLUS_TABLE           = 'DTM';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => 6],
		'description' => ['type' => 'text', 'maxlength' => 20],
	];

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
		$q->select(GlCode::aliasproperty('id'));
		return $q->find()->toArray();
	}

	/**
	 * Return the Code records from Database
	 * @return ObjectCollection
	 */
	public function codes() {
		$q = $this->getQueryClass();
		return $q->find();
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @return GlCode
	 */
	public function new($id = '') {
		$code = new GlCode();
		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		return $code;
	}

/* =============================================================
	CRUD Processing
============================================================= */
}
