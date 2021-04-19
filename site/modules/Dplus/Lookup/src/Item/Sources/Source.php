<?php namespace Dplus\Lookup\Item\Sources;
// Propel Classes
use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
//  ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Lookup
use Dplus\Lookup\Item\Input;
use Dplus\Lookup\Item\Sources\Results;

/**
 * Source
 * Template class for searching Database for Exact / LIKE item IDs
 */
abstract class Source extends WireData {
	const MODEL = '';
	const REQUIREDFIELDS = [];
	const SOURCE = '';

	/** @var Input */
	protected $inputdata;

	/** @var Results */
	protected $results;

/* =============================================================
	Getters / Setters
============================================================= */
	/**
	 * Set Input Data
	 * @param Input
	 */
	public function setInputData(Input $data) {
		$this->inputdata = $data;
	}

	/**
	 * Return Input Data
	 * @return Input
	 */
	public function getInputData() {
		return $this->inputdata;
	}

	/**
	 * Return Results
	 * @return Results
	 */
	public function getResults() {
		if (empty($this->results)) {
			return new Results();
		}
		return $this->results;
	}

	/**
	 * Return if Input Data has the required fields
	 * @return bool
	 */
	public function inputHasRequiredFields() {
		if (empty($this->inputdata)) {
			return false;
		}
		foreach (static::REQUIREDFIELDS as $key) {
			if ($this->inputdata->doesFieldHaveValue($key) === false) {
				return false;
			}
		}
		return true;
	}

/* =============================================================
	Query Functions
============================================================= */
	abstract protected function filterQuery(Query $q);

	/**
	 * Return Query Class Name
	 * @return string
	 */
	public static function queryClassName() {
		return static::MODEL.'Query';
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
	 * Return if Item ID is found
	 * @return bool
	 */
	public function find() {
		$this->results = new Results();
		$this->results->setInput($this->inputdata);

		if ($this->inputHasRequiredFields() === false) {
			$this->results->setMsg('Missing Required Fields');
			return false;
		}
		$q = $this->getFilteredQuery();
		$this->results->setExists($q->count() === 1);
		$this->results->setCount($q->count());

		if ($q->count() === 1) {
			$item = $q->findOne();
			$this->results->setItemid($item->itemid);
			return true;
		}
		return false;
	}

	/**
	 * Return Query Class with filters applied
	 * @return Query
	 */
	protected function getFilteredQuery() {
		$q = $this->getQueryClass();
		$this->filterQuery($q);
		return $q;
	}

	/**
	 * Return the Number of Matches Found
	 * @return int
	 */
	public function countMatches() {
		return 0;
	}
}
