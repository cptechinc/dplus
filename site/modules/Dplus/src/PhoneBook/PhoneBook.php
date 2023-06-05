<?php namespace Dplus\PhoneBook;
// Propel Classes
	// use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
	// use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Record;
	// use Propel\Runtime\Collection\ObjectCollection;
// Dplus Models
use PhoneBookQuery as Query;
use PhoneBook as Record;
	
// ProcessWire
use ProcessWire\WireData;

/**
 * PhoneBook
 * Class for querying phoneadr records from database
 */
class PhoneBook extends WireData {
	const MODEL              = 'PhoneBook';
	const MODEL_KEY          = '';
	const MODEL_TABLE        = 'phoneadr';
	const DESCRIPTION        = 'Phone Book Contact';

	protected static $instance;

	/** @return static */
	public static function instance() {
		if (empty(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
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
	 * Return model Class Name
	 * @return string
	 */
	public function modelClassName() {
		return static::MODEL;
	}

	/**
	 * Return Model
	 * @return Record
	 */
	public function newModel() {
		$class = $this->modelClassName();
		return new $class();
	}

	/**
	 * Return Model
	 * @return Record
	 */
	public function newRecord() {
		$class = $this->modelClassName();
		return new $class();
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
}