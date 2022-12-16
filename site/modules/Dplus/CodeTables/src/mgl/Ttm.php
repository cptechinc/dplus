<?php namespace Dplus\Codes\Mgl;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use GlTextCodeQuery, GlTextCode;
// Dplus Codes
use Dplus\Codes\AbstractCodeTableEditableSingleKey;

/**
 * Class that handles the CRUD of the TTM code table
 */
class Ttm extends AbstractCodeTableEditableSingleKey {
	const MODEL              = 'GlTextCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'gl_text_code';
	const DESCRIPTION        = 'Statement Text Code';
	const DESCRIPTION_RECORD = 'Statement Text Code';
	const RESPONSE_TEMPLATE  = 'Statement Text Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'ttm';
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
		$q->select(GlTextCode::aliasproperty('id'));
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
	 * @return GlTextCode
	 */
	public function new($id = '') {
		$code = new GlTextCode();
		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->string($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
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

		$code->setText1($values->text('text1', ['maxLength' => $this->fieldAttribute('text', 'maxlength')]));
		$code->setText2($values->text('text2', ['maxLength' => $this->fieldAttribute('text', 'maxlength')]));
		$code->setText3($values->text('text3', ['maxLength' => $this->fieldAttribute('text', 'maxlength')]));
		$code->setText4($values->text('text4', ['maxLength' => $this->fieldAttribute('text', 'maxlength')]));
		return $invalidfields;
	}
}
