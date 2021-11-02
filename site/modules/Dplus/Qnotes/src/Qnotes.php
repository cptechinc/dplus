<?php namespace Dplus\Qnotes;
// Propel ORM Library
use Propel\Runtime\Collection\ObjectCollection;

use ProcessWire\WireData;

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
}
