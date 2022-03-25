<?php namespace Dplus\Codes\Base;
// Purl URI Library
use Purl\Url;
// Propel Classes
use Propel\Runtime\ActiveQuery\CodeCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
use Propel\Runtime\Collection\ObjectCollection;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Codes
use Dplus\Codes\Response;
use Dplus\Codes\Base;

/**
 * Simple
 * Abstract class for Code Tables with Keys of (id)
 */
abstract class Simple extends Base {
	protected static $instance;

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Query Filtered By ID
	 * @param  string $id
	 * @return Query
	 */
	public function queryId($id) {
		$q = $this->query();
		$q->filterById($id);
		return $q;
	}

/* =============================================================
	CRUD Reads
============================================================= */
	/**
	 * Return the Code records from Database filtered by Code ID
	 * @param  string $id
	 * @return Code
	 */
	public function code($id) {
		$q = $this->query();
		return $q->findOneById($id);
	}

	/**
	 * Returns if Code Exists
	 * @param  string $id
	 * @return bool
	 */
	public function exists($id) {
		$q = $this->query();
		return boolval($q->filterById($id)->count());
	}

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

	/**
	 * Return Description for Code
	 * @param  string $id
	 * @return string
	 */
	public function description($id) {
		if ($this->exists($id) === false) {
			return '';
		}
		$model = static::modelClassName();
		$q = $this->queryId($id);
		$q->select($model::aliasproperty('description'));
		return $q->findOne();
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
		$dplusdb = $this->wire('modules')->get('DplusDatabase')->db_name;
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
