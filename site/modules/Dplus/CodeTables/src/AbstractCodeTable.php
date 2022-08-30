<?php namespace Dplus\Codes;
// Propel Classes
use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
use Propel\Runtime\Collection\ObjectCollection;
// ProcessWire
use ProcessWire\WireData;

/**
 * AbstractCodeTable
 * Class for Reading Codes from database
 */
abstract class AbstractCodeTable extends WireData {
	const MODEL              = '';
	const MODEL_KEY          = '';
	const MODEL_TABLE        = '';
	const DESCRIPTION        = '';
	const DESCRIPTION_RECORD = '';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => 4, 'label' => 'Code'],
		'description' => ['type' => 'text', 'maxlength' => 20, 'label' => 'Description'],
	];
	const FILTERABLE_FIELDS = ['code', 'description'];

	protected static $instance;

	public static function instance() {
		return static::getInstance();
	}

	public static function getInstance() {
		if (empty(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * Return Array ready for JSON
	 * @param  Code  $code Code
	 * @return array
	 */
	public function codeJson(Code $code) {
		return ['code' => $code->code, 'description' => $code->description];
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

	/**
	 * Return List of filterable fields
	 * @return array
	 */
	public function filterableFields() {
		return static::FILTERABLE_FIELDS;
	}

	/**
	 * Return Label for field
	 * @param  string $field
	 * @return string
	 */
	public function fieldLabel($field) {
		$label = $this->fieldAttribute($field, 'label');

		if ($label !== false) {
			return $label;
		}

		if (in_array($field, ['code', 'description'])) {
			return self::FIELD_ATTRIBUTES[$field]['label'];
		}
		return $field;
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
	 * Returns the associated CodeQuery class for table code
	 * @return mixed
	 */
	public function query() {
		return $this->getQueryClass();
	}

/* =============================================================
	CRUD Reads
============================================================= */
	/**
	 * Return the Code records from Database
	 * @return ObjectCollection
	 */
	public function codes() {
		$q = $this->query();
		return $q->find();
	}
}
