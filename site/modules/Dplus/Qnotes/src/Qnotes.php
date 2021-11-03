<?php namespace Dplus\Qnotes;
// Propel ORM Library
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
// ProcessWire
use ProcessWire\WireData;
use ProcessWire\WireInput;

abstract class Qnotes extends WireData {
	const MODEL                = '';
	const MODEL_KEY            = 'id';
	const DESCRIPTION          = 'Notes';
	const DESCRIPTION_RECORD   = 'Notes';
	const DESCRIPTION_RESPONSE = 'Note ';
	const TYPE                 = '';

	const FIELD_ATTRIBUTES = [
		'note' => ['type' => 'text', 'maxlength' => 50],
	];

	protected static $instance;

	public static function getInstance() {
		if (empty(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	public function __construct() {
		$this->sessionID = session_id();
	}

	/**
	 * Returns Lines Broken down by line limits
	 * @param  string $note   Text Area Note
	 * @param  int    $length Line Length Limit
	 * @return array
	 */
	public function explodeNoteLines($note, int $length = 0) {
		$lines = [];

		if ($length) {
			$wrapped = wordwrap($note, $length, PHP_EOL, $cut = true);
			$lines = explode(PHP_EOL, $wrapped);
		} else {
			$lines = explode(PHP_EOL, $note);
		}
		return $lines;
	}

/* =============================================================
	Field Configs
============================================================= */
	/**
	 * Return Field Attribute value
	 * @param  string $field Field Name
	 * @param  string $attr  Attribute Name
	 * @return mixed|bool
	 */
	public function fieldAttribute($field = '', $attr = '') {
		if (empty($field) || empty($attr)) {
			return false;
		}
		if (array_key_exists($field, static::FIELD_ATTRIBUTES) === false) {
			return false;
		}
		if (array_key_exists($attr, static::FIELD_ATTRIBUTES[$field]) === false) {
			return false;
		}
		return static::FIELD_ATTRIBUTES[$field][$attr];
	}

/* =============================================================
	Model Functions
============================================================= */
	/**
	 * Return Nodel Class Name
	 * @return string
	 */
	public function modelClassName() {
		return $this::MODEL;
	}

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Query Class Name
	 * @return string
	 */
	public function queryClassName() {
		return $this::MODEL.'Query';
	}

	/**
	 * Return New Query Class
	 * @return Query
	 */
	public function getQueryClass() {
		$class = static::queryClassName();
		return $class::create();
	}

	/**
	 * Returns the associated Query class for note table
	 * @return mixed
	 */
	public function query() {
		return $this->getQueryClass();
	}

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Returns Line 1 of Every Note
	 * @return ObjectCollection
	 */
	public function getSummarizedNotes() {
		$q = $this->query();
		$q->filterBySequence(1);
		return $q->find();
	}

/* =============================================================
	CRUD Processing
============================================================= */
	/**
	 * Process Input Data, Update Database
	 * @param  WireInput $input Input Data
	 */
	public function processInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		switch ($values->text('action')) {
			case 'delete':
				$this->inputDelete($input);
				break;
			case 'update':
				$this->inputUpdate($input);
				break;
		}
	}

	/**
	 * Update Qnotes from Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function inputUpdate(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		return $this->_inputUpdate($input);
	}

	/**
	 * Delete Qnotes from Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function inputDelete(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		return $this->_inputDelete($input);
	}

/* =============================================================
	CRUD Response
============================================================= */
	/**
	 * Set Session Response
	 * @param Response $response
	 */
	public function setResponse(Response $response) {
		$this->wire('session')->setFor('response', static::TYPE, $response);
	}

	/**
	 * Return Session Response
	 * @return Response
	 */
	public function getResponse() {
		return $this->wire('session')->getFor('response', static::TYPE);
	}

	/**
	 * Delete Session Response
	 */
	public function deleteResponse() {
		$this->wire('session')->removeFor('response', static::TYPE);
	}

/* =============================================================
	Dplus Requests
============================================================= */
	/**
	 * Return Data needed for Dplus to UPDATE the Qnote
	 * @param  string $notetype Note Type @see WarehouseNote::TYPES
	 * @param  string $key2     Key 2
	 * @param  string $form     Form e.g YNNN
	 * @return array
	 */
	public function writeRqstData(ActiveRecordInterface $note) {
		$dplusdb = $this->wire('modules')->get('DplusDatabase')->db_name;
		return ["DBNAME=$dplusdb", 'UPDATEQNOTE', "TYPE=$note->type", "KEY2=$note->key2", "FORM=$note->form"];
	}

	/**
	 * Sends Update Request for Qnote
	 * @param  ActiveRecordInterface $note
	 * @return void
	 */
	public function updateDplus(ActiveRecordInterface $note) {
		$config = $this->wire('config');
		$data   = $this->writeRqstData($note);
		$requestor = $this->wire('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $this->sessionID);
		$requestor->cgi_request($config->cgis['database'], $this->sessionID);
	}
}
