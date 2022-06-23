<?php namespace Dplus\Docm\Finders\Finder;
// Dplus Models
use DocumentQuery as Query;
// Dplus Docm
use Dplus\Docm\Documents;
use Dplus\Docm\Finder;


/**
 * Finder\Tag
 * Decorator for DocumentQuery to find Documents in Database related to a tag
 */
abstract class Tag extends Finder {
	const TAG = [];

	/**
	 * Return Query Filtered By Tag(s)
	 * @return Query
	 */
	public function query() {
		return Documents::instance()->queryTag(static::TAG);
	}

	/**
	 * Return Query
	 * @return Query
	 */
	public function queryBase() {
		return Documents::instance()->query();
	}

/* =============================================================
	Read Functions
============================================================= */
	/**
	 * Return Documents
	 * filtered by the tag1, reference1 fields for a Quote
	 * @param  string $qnbr                  Quote Number
	 * @return Documents[]|ObjectCollection
	 */
	public function find($qnbr) {
		$q = $this->query();
		$q->filterByReference1($this->qnbr($qnbr));
		return $q->find();
	}

	/**
	 * Return the number of Documents
	 * filtered by the tag1, reference1 fields for a Quote
	 * @param  string $qnbr Quote Number
	 * @return int
	 */
	public function count($qnbr) {
		$q = $this->query();
		$q->filterByReference1($this->qnbr($qnbr));
		return $q->count();
	}	
}