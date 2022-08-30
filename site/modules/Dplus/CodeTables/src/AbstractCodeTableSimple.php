<?php namespace Dplus\Codes;
// Propel Classes
use Propel\Runtime\ActiveQuery\CodeCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
  // use Propel\Runtime\Collection\ObjectCollection;


/**
 * AbstractCodeTableSimple 
 * Class for Reading Codes that have single column keys from database
 */
abstract class AbstractCodeTableSimple extends AbstractCodeTable {
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
}
