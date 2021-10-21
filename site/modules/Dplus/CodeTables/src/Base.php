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
	const MODEL_TABLE        = '';
	const DESCRIPTION        = '';
	const DESCRIPTION_RECORD = '';
	const RECORDLOCKER_FUNCTION = 'cxm';
	const DPLUS_TABLE           = '';

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

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Sends Dplus Cobol that Code Table has been Update
	 * @param  string $table Code Table
	 * @param  string $code  Code
	 * @return void
	 */
	protected function updateDplus($code) {
		$config  = $this->wire('config');
		$dplusdb = $this->wire('modules')->get('DplusDatabase')->db_name;
		$table = static::DPLUS_TABLE;
		$data = ["DBNAME=$dplusdb", 'UPDATECODETABLE', "TABLE=$table", "CODE=$code"];
		$requestor = $this->wire('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, session_id());
		$requestor->cgi_request($config->cgis['database'], session_id());
	}
}