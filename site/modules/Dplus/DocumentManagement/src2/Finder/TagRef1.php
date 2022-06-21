<?php namespace Dplus\Docm\Finder;

/**
 * Finder\Tags\TagRef1
 * Decorator (simple) for DocumentQuery to find Documents in Database related to a tag, reference1
 */
abstract class TagRef1 extends Tag {
	const TAG = [];

/* =============================================================
	Read Functions
============================================================= */
	/**
	 * Return Documents
	 * filtered by the tag1, reference1 fields for a Quote
	 * @param  string $ref1  Reference 1 Value
	 * @return Document[]|ObjectCollection
	 */
	public function find($ref1) {
		$q = $this->query();
		$q->filterByReference1($ref1);
		return $q->find();
	}

	/**
	 * Return the number of Documents
	 * filtered by the tag1, reference1 fields for a Quote
	 * @param  string $ref1  Reference 1 Value
	 * @return int
	 */
	public function count($ref1) {
		$q = $this->query();
		$q->filterByReference1($ref1);
		return $q->count();
	}
}