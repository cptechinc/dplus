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
	/** Set $this->query **/
	abstract public function initQuery();

	/** Filter Query with Input Data **/


	/** Filter Columns using a Wildcard Search **/
	abstract public function _search($q);

/* =============================================================
	Functions
============================================================= */
	public function __construct() {
		$model = $this::MODEL.'Query';
		$this->query = $model::create();
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
