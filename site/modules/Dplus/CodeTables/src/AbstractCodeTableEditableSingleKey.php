<?php namespace Dplus\Codes;
// Propel Classes
  // use Propel\Runtime\ActiveQuery\CodeCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
  // use Propel\Runtime\Collection\ObjectCollection;
// ProcessWire
use ProcessWire\WireInput;
// Dplus Codes
use Dplus\Codes\Response;

/**
 * AbstractCodeTableEditableSingleKey
 * 
 * Handles Editing of single-Keyed CodeTables
 */
abstract class AbstractCodeTableEditableSingleKey extends AbstractCodeTableEditable {
	use SingleKeyTraits;

	const RECORDLOCKER_FUNCTION = '';
	const RESPONSE_TEMPLATE     = 'Code {code} {not} {crud}';
	const DPLUS_TABLE           = '';
	const PERMISSION            = '';

	protected static $instance;


/* =============================================================
	CRUD Reads
============================================================= */
	/**
	 * Return New or Existing Code
	 * @param  string $id  Code ID
	 * @return Code
	 */
	public function getOrCreate($id = '') {
		if ($this->exists($id)) {
			return $this->code($id);
		}
		return $this->new($id);
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @param  string $id
	 * @return Code
	 */
	public function new($id = '') {
		$class = $this->modelClassName();
		$code = new $class();
		$maxlength = $this->fieldAttribute('code', 'maxlength');

		if ($maxlength) {
			$id = $this->wire('sanitizer')->string($id, ['maxLength' => $maxlength]);
		}
		if (empty($id) === false && $id != 'new') {
			$code->setId($id);
		}
		$code->setDummy('P');
		return $code;
	}

/* =============================================================
	CRUD Processing
============================================================= */
	/**
	 * Update Code from Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function inputUpdate(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$id     = $values->string('code', ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
		$invalidfields = [];

		$code          = $this->getOrCreate($id);
		$invalidfields = $this->_inputUpdate($input, $code);
		$response      = $this->saveAndRespond($code, $invalidfields);
		$this->setResponse($response);
		return $response->hasSuccess();
	}

	/**
	 * Delete Code
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function inputDelete(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$id     = $values->string('code', ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);

		if ($this->exists($id) === false) {
			$response = Response::responseSuccess("Code $id was deleted");
			$response->buildMessage(static::RESPONSE_TEMPLATE);
			$response->setCode($id);
			return true;
		}
		$code = $this->code($id);
		$code->delete();
		$response = $this->saveAndRespond($code);
		$this->setResponse($response);
		return $response->hasSuccess();
	}
}
