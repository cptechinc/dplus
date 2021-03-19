<?php namespace Dplus\Filters;
// Propel Classes
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
	 * Return Position of Record in results
	 * @param  Model $record (Record Class)
	 * @return int
	 */
	public function position(Model $record) {
		$results = $this->query->find();
		return $results->search($record);
	}
}
