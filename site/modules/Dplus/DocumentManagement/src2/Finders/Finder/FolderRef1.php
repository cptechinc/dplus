<?php namespace Dplus\Docm\Finders\Finder;
// Dplus Docm
use Dplus\Docm\Finder\Folder;


/**
 * Finder\Tags\TagRef1
 * Decorator (simple) for DocumentQuery to find Documents in Database related to a folder, reference1
 */
abstract class FolderRef1 extends Folder {
	const FOLDER = [];

/* =============================================================
	Read Functions
============================================================= */
	/**
	 * Return Documents
	 * filtered by the folder, reference1 fields for a Quote
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
	 * filtered by the folder, reference1 fields for a Quote
	 * @param  string $ref1  Reference 1 Value
	 * @return int
	 */
	public function count($ref1) {
		$q = $this->query();
		$q->filterByReference1($ref1);
		return $q->count();
	}	
}