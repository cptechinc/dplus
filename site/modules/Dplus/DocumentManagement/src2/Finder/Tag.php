<?php namespace Dplus\Docm\Finder;
// Dplus Models
use DocumentQuery as Query;
// ProcessWire
use ProcessWire\WireData;
// Dplus Docm
use Dplus\Docm\Documents;


/**
 * Finder\Tag
 * Decorator for DocumentQuery to find Documents in Database related to a tag
 */
abstract class Tag extends WireData {
	const TAG = [];

	/**
	 * Return Query Filtered By Tag(s)
	 * @return Query
	 */
	public function query() {
		return Documents::instance()->queryTag(static::TAG);
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
	 * @return int          Number of Sales Order Documents found
	 */
	public function count($qnbr) {
		$q = $this->query();
		$q->filterByReference1($this->qnbr($qnbr));
		return $q->count();
	}	
}