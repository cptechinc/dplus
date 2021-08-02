<?php namespace Dplus\Filters;
use PDO;
// Propel Classes
use Propel\Runtime\Propel;
use Propel\Runtime\Connection\StatementWrapper;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Model;
//  ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;

/**
 * Base Filter Class
 * |
 * | Child Classes should be Organized in the Following Manner:
 * | 1. Abstract Contract / Extensible Functions
 * | 2. Base Filter Functions
 * | 3. Input Filter Classes
 * | 4. Misc Query Functions
 * |
 * @property Query $query Query to filter
 */
abstract class AbstractFilter extends WireData {
	const MODEL = '';

	public $query;

/* =============================================================
	Abstract Functions
============================================================= */
	/** Filter Columns using a Wildcard Search **/
	abstract public function _search($q);

/* =============================================================
	Extensible Functions
============================================================= */
	/**
	 * Filter Query with Input Data
	 * @param  WireInput $input Input Data
	 * @return self
	 */
	public function _filterInput(WireInput $input) {

	}

/* =============================================================
	Functions
============================================================= */
	public function __construct() {
		$this->_initQuery();
	}

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
	 * Return Query Class for self::MODEL
	 * @return Query
	 */
	public function _initQuery() {
		$this->query = $this->getQueryClass();
	}

	/**
	 * Set and Initialize $this->query
	 * @return void
	 */
	public function initQuery() {
		$this->_initQuery();
	}

	/**
	 * Returns Query
	 * @return Query
	 */
	public function query() {
		return $this->query;
	}

	/**
	 * Initializes Query
	 * @return self
	 */
	public function init() {
		$this->initQuery();
		return $this;
	}

	/**
	 * Filter Query with Input Data
	 * @param  WireInput $input Input Data
	 * @return self
	 */
	public function filterInput(WireInput $input) {
		$this->_filterInput($input);
		return $this;
	}

	/**
	 * Do a Wildcard search against columns
	 * @param  string $q Search Query
	 * @return self
	 */
	public function search($q) {
		$this->_search($q);
		return $this;
	}

	/**
	 * Adds the Sort By to the query
	 * @param  Page   $page
	 * @return void
	 */
	public function sortby(Page $page) {
		if ($page->has_orderby()) {
			$orderbycolumn = $page->orderby_column;
			$sort = $page->orderby_sort;
			$model = $this::MODEL;
			$tablecolumn = $model::aliasproperty($orderbycolumn);
			$this->query->sortBy($tablecolumn, $sort);
		}
	}

	/**
	 * Add Sort By to the Query
	 * @param  string $col   Column
	 * @param  string $sort  Sort By
	 * @return void
	 */
	public function orderBy($col, $sort) {
		$model = $this::MODEL;
		$tablecolumn = $model::aliasproperty($col);
		$this->query->sortBy($tablecolumn, $sort);
	}

	/**
	 * Return Position of Record in results
	 * @param  Model $record (Record Class)
	 * @return int
	 */
	public function position(Model $record) {
		$results = $this->query->find();
		$position = $results->search($record);
		unset($results);
		return $position;
	}

	/**
	 * Apply Sort and Searches to the Query
	 * @param  SortFilter $sortFilter
	 * @return void
	 */
	public function applySortFilter(SortFilter $sortFilter = null) {
		if ($sortFilter) {
			if ($sortFilter->q) {
				$this->search(strtoupper($sortFilter->q));
			}

			if ($sortFilter->orderby) {
				$data = explode('-', $sortFilter->orderby);
				$this->orderBy($data[0], $data[1]);
			}
		}
	}

/* =============================================================
	Functions
============================================================= */
	/**
	 * Return Where clause as a string by executing $this->query->count();
	 * @return string
	 */
	public function getWhereClauseString()  {
		$this->query->count();
		$con = Propel::getWriteConnection($this->query->getDbName());
		$sql = $con->getLastExecutedQuery();

		if (strpos($sql, 'WHERE') !== false) {
			$parts = explode(' WHERE ', $sql);
			return $parts[1];
		}
		return '';
	}

	/**
	 * Return Where Clause
	 * @return array
	 */
	protected function getWhereClause() {
		return [];

		$params = $this->query->getParams();
		$whereClause = [];

		$dbMap   = Propel::getServiceContainer()->getDatabaseMap($this->query->getDbName());
		$adapter = Propel::getServiceContainer()->getAdapter($this->query->getDbName());

		foreach ($this->query->keys() as $key) {
			$criterion = $this->query->getCriterion($key);
			$table = null;

			foreach ($criterion->getAttachedCriterion() as $attachedCriterion) {
				$tableName = $attachedCriterion->getTable();

				$table = $this->query->getTableForAlias($tableName);
				if ($table !== null) {
				} else {
					$table = $tableName;
				}

				if ($this->query->isIgnoreCase() && method_exists($attachedCriterion, 'setIgnoreCase') && $dbMap->getTable($table)->getColumn($attachedCriterion->getColumn())->isText()) {
					$attachedCriterion->setIgnoreCase(true);
				}
			}

			$criterion->setAdapter($adapter);

			$sb = '';
			$criterion->appendPsTo($sb, $params);
			$this->query->replaceNames($sb);
			$whereClause[] = $sb;
		}
		return $whereClause;
	}

	/**
	 * Return Order By Clause
	 * @return string
	 */
	protected function getOrderByClause() {
		$dbMap   = Propel::getServiceContainer()->getDatabaseMap($this->query->getDbName());
		$adapter = Propel::getServiceContainer()->getAdapter($this->query->getDbName());
		$orderByClause = [];
		$orderBy = $this->query->getOrderByColumns();

		if (!empty($orderBy)) {
			foreach ($orderBy as $orderByColumn) {
				// Add function expression as-is.
				if (strpos($orderByColumn, '(') !== false) {
					$orderByClause[] = $orderByColumn;
					continue;
				}

				// Split orderByColumn (i.e. "table.column DESC")
				$dotPos = strrpos($orderByColumn, '.');

				if ($dotPos !== false) {
					$tableName = substr($orderByColumn, 0, $dotPos);
					$columnName = substr($orderByColumn, $dotPos + 1);
				} else {
					$tableName = '';
					$columnName = $orderByColumn;
				}

				$spacePos = strpos($columnName, ' ');

				if ($spacePos !== false) {
					$direction = substr($columnName, $spacePos);
					$columnName = substr($columnName, 0, $spacePos);
				} else {
					$direction = '';
				}

				$tableAlias = $tableName;
				$aliasTableName = $this->query->getTableForAlias($tableName);
				if ($aliasTableName) {
					$tableName = $aliasTableName;
				}

				$columnAlias = $columnName;
				$asColumnName = $this->query->getColumnForAs($columnName);
				if ($asColumnName) {
					$columnName = $asColumnName;
				}

				$column = $tableName ? $dbMap->getTable($tableName)->getColumn($columnName) : null;

				if ($this->query->isIgnoreCase() && $column && $column->isText()) {
					$ignoreCaseColumn = $adapter->ignoreCaseInOrderBy("$tableAlias.$columnAlias");
					$this->query->replaceNames($ignoreCaseColumn);
					$orderByClause[] = $ignoreCaseColumn . $direction;
					$selectSql .= ', ' . $ignoreCaseColumn;
				} else {
					$this->query->replaceNames($orderByColumn);
					$orderByClause[] = $orderByColumn;
				}
			}
		}
		return $orderByClause;
	}

	/**
	 * Bind Query Parameters to Statement
	 * @param  StatementWrapper $stmt
	 * @param  int              $position
	 * @return void
	 */
	protected function bindQueryParamsToStmt(StatementWrapper $stmt, int $params = 0) {
		$position = $params ? $params : $this->countParams();

		foreach ($this->query->getParams() as $key => $param) {
			$position++;
			$parameter = ':p' . $position;
			$value = $param['value'];
			if ($value === null) {
				$stmt->bindValue($parameter, null, PDO::PARAM_NULL);
				continue;
			}
			$tableName = $param['table'];
			$type = isset($param['type']) ? $param['type'] : PDO::PARAM_STR;
			$stmt->bindValue($parameter, $value, $type);
		}
	}

	/**
	 * Return the number of parameters
	 * @return int
	 */
	protected function countParams() {
		$whereClause = implode(' ', $this->getWhereClause());
		return substr_count($whereClause, ':p');
	}

	/**
	 * Return Propel Write Connection
	 * @return ConnectionInterface;
	 */
	protected function getWriteConnection() {
		return Propel::getWriteConnection($this->query->getDbName());
	}

	/**
	 * Return Propel Statement Wrapper
	 * @return StatementWrapper
	 */
	protected function getPreparedStatementWrapper($sql) {
		return $this->getWriteConnection()->prepare($sql);
	}
}
