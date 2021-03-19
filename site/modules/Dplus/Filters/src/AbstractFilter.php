<?php namespace Dplus\Filters;

use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
use Propel\Runtime\ActiveQuery\ModelCriteria;

/**
 * Base Filter Class
 *
 * @property ModelCriteria $query Query to filter
 */
abstract class AbstractFilter extends WireData {
	const MODEL = '';

	public $query;

/* =============================================================
	Abstract Functions
============================================================= */


	/** Filter Query with Input Data **/


	/** Filter Columns using a Wildcard Search **/
	abstract public function _search($q);

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
	 * Return Query Class for self::MODEL
	 * @return ModelCriteria
	 */
	public function _initQuery() {
		$class = self::queryClassName();
		$this->query = $class::create();
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
	 * @return ModelCriteria
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
	 * Filter Query with Input Data
	 * @param  WireInput $input Input Data
	 * @return self
	 */
	public function _filterInput(WireInput $input) {

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
			$tablecolumn = $model::get_aliasproperty($orderbycolumn);
			$this->query->sortBy($tablecolumn, $sort);
		}
	}
}
