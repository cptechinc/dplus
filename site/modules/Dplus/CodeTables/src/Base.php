<?php namespace Dplus\Codes;
// Purl URI Library
use Purl\Url;
// Propel Classes
use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Model;
use Propel\Runtime\Collection\ObjectCollection;
// ProcessWire
use ProcessWire\WireData;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as FunctionLocker;

abstract class Base extends WireData {
	const MODEL              = '';
	const MODEL_KEY          = '';
	const DESCRIPTION        = '';
	const DESCRIPTION_RECORD = '';
	const TABLE              = '';
	const RECORDLOCKER_FUNCTION = 'cxm';

	protected static $instance;

	public static function getInstance() {
		if (empty(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	public function __construct() {
		$this->recordlocker = new FunctionLocker();
		$this->recordlocker->setFunction(self::RECORDLOCKER_FUNCTION);
		$this->recordlocker->setUser($this->wire('user'));
	}

	/**
	 * Return Array ready for JSON
	 * @param  Model  $code Code
	 * @return array
	 */
	public function codeJson(Model $code) {
		return ['code' => $code->code, 'description' => $code->description];
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
		$class = self::queryClassName();
		return $class::create();
	}

	/**
	 * Returns the associated ModelQuery class for table code
	 * @return mixed
	 */
	public function query() {
		return $this->getQueryClass();
	}
}
