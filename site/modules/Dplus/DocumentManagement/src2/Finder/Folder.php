<?php namespace Dplus\Docm\Finder;
// Dplus Models
use DocumentQuery as Query;
// Dplus Docm
use Dplus\Docm\Documents;
use Dplus\Docm\Finder;


/**
 * Finder\Tag
 * Decorator for DocumentQuery to find Documents in Database related to a tag
 */
abstract class Folder extends Finder {
	const FOLDER = [];

	/**
	 * Return Query Filtered By Folder(s)
	 * @return Query
	 */
	public function query() {
		return Documents::instance()->queryTag(static::FOLDER);
	}

/* =============================================================
	Read Functions
============================================================= */
	/**
	 * Return Documents
	 * @return Documents[]|ObjectCollection
	 */
	public function findAll() {
		$q = $this->query();
		return $q->find();
	}

	/**
	 * Return the number of Documents
	 * @return int
	 */
	public function countAll() {
		$q = $this->query();
		return $q->count();
	}	
}