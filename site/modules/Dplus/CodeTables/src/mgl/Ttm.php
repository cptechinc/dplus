<?php namespace Dplus\Codes\Mgl;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use GLTextCodeQuery, GLTextCode;
// Dplus Codes
use Dplus\Codes\Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the TTM code table
 */
class Ttm extends Base {
	const MODEL              = 'GLTextCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'gl_text_code';
	const DESCRIPTION        = 'Statement Text Code';
	const DESCRIPTION_RECORD = 'Statement Text Code';
	const RESPONSE_TEMPLATE  = 'Statement Text Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'text';
	const DPLUS_TABLE           = 'TTM';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => 6],
		'text'        => ['type' => 'text', 'maxlength' => 35],
	];

	protected static $instance;

	/**
	 * Return Array ready for JSON
	 * @param  Code  $code Code
	 * @return array
	 */
	public function codeJson(Code $code) {
		return [
			'code' => $code->code,
			'text1' => $code->text1,
			'text2' => $code->text2,
			'text3' => $code->text3,
			'text4' => $code->text4,
		];
	}

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return the IDs for the Work Center Confirm Code
	 * @return array
	 */
	public function ids() {
		$q = $this->query();
		$q->select(GLTextCode::aliasproperty('id'));
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
	 * @return GLTextCode
	 */
	public function new($id = '') {
		$code = new GLTextCode();
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
		$dcm = Dcm::getInstance();
		if ($values->text('workcenterid') != '' && $dcm->exists($values->text('workcenterid')) === false) {
			$invalidfields['workcenterid'] = "Work Center";
			return $invalidfields;
		}
		$code->setWorkcenterid($values->text('workcenterid'));
		return $invalidfields;
	}
}
