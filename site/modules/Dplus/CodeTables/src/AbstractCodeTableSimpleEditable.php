<?php namespace Dplus\Codes;
// Propel Classes
  // use Propel\Runtime\ActiveQuery\CodeCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
  // use Propel\Runtime\Collection\ObjectCollection;
// ProcessWire
use ProcessWire\WireInput;
// Dplus Codes
use Dplus\Codes\Response;
// Dplus Databases
use Dplus\Databases\Connectors\Dplus as DbDplus;

/**
 * AbstractCodeTableSimpleEditable
 * 
 * Handles Editing of single-Keyed CodeTables
 */
abstract class AbstractCodeTableSimpleEditable extends AbstractCodeTableSimple {
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
		$code = new Code();
		$maxlength = $this->fieldAttribute('code', 'maxlength');

		if ($maxlength) {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $maxlength]);
		}
		if (empty($id) === false) {
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
		$id     = $values->text('code', ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
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
		$id     = $values->text('code', ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);

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

/* =============================================================
	Dplus Requests
============================================================= */
	/**
	 * Return Request Data Neeeded for Dplus Update
	 * @param  Code $code  Code
	 * @return array
	 */
	protected function generateRequestData($code) {
		$dplusdb = DbDplus::instance()->dbconfig->dbName;
		$table   = static::DPLUS_TABLE;
		return ["DBNAME=$dplusdb", 'UPDATECODETABLE', "TABLE=$table", "CODE=$code->id"];
	}

	/**
	 * Send Request do Dplus
	 * @param  array  $data  Request Data
	 * @return void
	 */
	protected function sendDplusRequest(array $data) {
		$config    = $this->wire('config');
		$requestor = $this->wire('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $this->sessionID);
		$requestor->cgi_request($config->cgis['database'], $this->sessionID);
	}

	/**
	 * Sends Dplus Cobol that Code Table has been Update
	 * @param  Code $code  Code
	 * @return void
	 */
	protected function updateDplus($code) {
		$data = $this->generateRequestData($code);
		$this->sendDplusRequest($data);
	}
}
