<?php namespace Pauldro\ProcessWire\DatabaseTables;
// ProcessWire
use ProcessWire\WireData;
use ProcessWire\DatabaseQuerySelect;
// Pauldro ProcessWire
use Pauldro\ProcessWire\DatabaseQuery\DatabaseQueryCreateTable;
use Pauldro\ProcessWire\DatabaseQuery\DatabaseQueryInsert;
use Pauldro\ProcessWire\DatabaseTable\Model;

/**
 * DatabaseTable
 * Handles CRUD actions for a Custom ProcessWire Database Table
 */
abstract class AbstractDatabaseTable extends WireData {
	const TABLE = '';
	const FORMAT_DATETIME = 'Y-m-d H:i:s';
	const COLUMNS = [];
	const PRIMARYKEY = [];
	const MODEL_CLASS = '\\Pauldro\\ProcessWire\\DatabaseTable\\Model';

	/** @var static */
	protected static $instance;

	/** @return static */
	public static function instance() {
		if (empty(static::$instance)) {
			$instance = new static();
			static::$instance = $instance;
			static::$instance->init();
		}
		return static::$instance;
	}

	public function init() {
		$this->install();
	}

	/**
	 * Install Table in Database
	 * @return void
	 */
	public function install() {
		if ($this->wire('database')->tableExists(static::TABLE) === false) {
			return $this->createTable();
		}
		return true;
	}

	/**
	 * Create Table in database
	 * @return bool
	 */
	public function createTable() {
		$q = new DatabaseQueryCreateTable();
		$q->table(static::TABLE);
		$q->columns(static::COLUMNS);
		$q->primarykey(static::PRIMARYKEY);
		return boolval($q->execute()->rowCount());
	}

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Query
	 * @return DatabaseQuerySelect
	 */	
	protected function query() {
		$q = new DatabaseQuerySelect();
		$q->from(static::TABLE);
		return $q;
	}

	/**
	 * Return Query Filtered by Primary Key
	 * @param  array $key
	 * @return DatabaseQuerySelect
	 */
	protected function queryPrimaryKey(array $key) {
		$data = $this->new();
		$data->setData($key);
		
		$where  = $this->getParamsForQuery(static::PRIMARYKEY);
		$params = $this->getParamKeyValues($data, static::PRIMARYKEY);

		$q = $this->query();
		$q->where($where, $params);
		return $q;
	}

	/**
	 * Return instance of Model
	 * @return Model
	 */
	public function new() {
		$class = static::MODEL_CLASS;
		return new $class();
	}

/* =============================================================
	Read Functions
============================================================= */
	/**
	 * Return the total number of records
	 * @return int
	 */
	public function countAll() {
		$q = $this->query();
		$q->select('COUNT(*)');
		return intval($q->execute()->fetchColumn());
	}
	
	/**
	 * Return if Record Exists
	 * @param  array $key
	 * @return bool
	 */
	public function existsByPrimaryKey(array $key) {
		$q = $this->queryPrimaryKey($key);
		$q->select('COUNT(*)');
		return boolval($q->execute()->fetchColumn());
	}

	/**
	 * Return All Records
	 * @return array[Model]
	 */
	public function findAll() {
		$q = $this->query();
		$q->select('*');
		return $q->execute()->fetchObject(self::MODEL_CLASS);
	}

	/**
	 * Return if Record Exists
	 * @param  string $key
	 * @return bool
	 */
	abstract public function exists($key);

	/**
	 * Return if Record Data already exists
	 * @param  array $record
	 * @return bool
	 */
	abstract public function existsArray(array $record);

/* =============================================================
	Create, Update Functions
============================================================= */
	/**
	 * Insert Record
	 * @param  Model $data
	 * @return bool
	 */
	public function insert(Model $data) {
		$q = new DatabaseQueryInsert();
		$q->insertInto(static::TABLE);
		$q->columns(array_keys($data->data));
		$q->values($this->getParamKeysString($data), $this->getParamKeyValues($data));
		return boolval($q->execute()->rowCount());
	}

	/**
	 * Insert Record 
	 * @param  array $record
	 * @return bool
	 */
	public function insertArray(array $record) {
		if ($this->validateRecordKeys($record) === false) {
			return false;
		}
		$model = $this->new();
		$model->setArray($record);
		return $this->insert($model);
	}

/* =============================================================
	Param, Key Functions
============================================================= */
	/**
	 * Return Parameters for Set
	 * @param  array $cols
	 * @return string		column=:column
	 */
	protected function getParamsForQuery($cols) {
		$data = [];
		foreach ($cols as $col) {
			$data[] = "$col=:$col";
		}
		return implode(',', $data);
	}

	/**
	 * Return Parameters Key String
	 * @param  Model    $data
	 * @return string		  :col1,:col2
	 */
	protected function getParamKeysString(Model $data) {
		return ':' . implode(',:', array_keys($data->data));
	}

	/**
	 * Return Parameters Keyed by Param Key
	 * @param  Model  $data
	 * @param  array $keys	
	 * @return array		   [':key' => $value]
	 */
	protected function getParamKeyValues(Model $data, $keys = []) {
		$params = [];

		foreach ($data->data as $key => $value) {
			if (in_array($key, $keys) || empty($keys)) {
				$params[':' . $key] = $value;
			}
		}
		return $params;
	}

	/**
	 * Return if Record has the needed column as keys
	 * @param  array $record
	 * @return bool
	 */
	protected function validateArrayKeys(array $record) {
		foreach (array_keys(static::COLUMNS) as $col) {
			if (array_key_exists($col, $record) === false) {
				return false;
			}
		}
		return true;
	}
}