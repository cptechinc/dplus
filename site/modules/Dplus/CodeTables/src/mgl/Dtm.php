<?php namespace Dplus\Codes\Mgl;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use GlDistCodeQuery, GlDistCode;
// Dplus Codes
use Dplus\Codes\Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the TTM code table
 */
class Dtm extends Base {
	const MODEL              = 'GlDistCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'gl_dist_code';
	const DESCRIPTION        = 'Distribution Code';
	const DESCRIPTION_RECORD = 'Distribution Code';
	const RESPONSE_TEMPLATE  = 'Distribution Code {code} {not} {crud}';
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
		$q->select(GlDistCode::aliasproperty('id'));
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
	 * @return GlDistCode
	 */
	public function new($id = '') {
		$code = new GlDistCode();
		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		return $code;
	}

/* =============================================================
	CRUD Processing
============================================================= */
	/**
	 * Update Record with Input Data
	 * @param  WireInput $input Input Data
	 * @param  Code      $code
	 * @return array
	 */
	protected function _inputUpdate(WireInput $input, Code $code) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$invalidfields = parent::_inputUpdate($input, $code);
		return $invalidfields;
	}
}
