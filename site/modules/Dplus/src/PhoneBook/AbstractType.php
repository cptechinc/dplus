<?php namespace Dplus\PhoneBook;
// Propel Classes
	// use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
	// use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Record;
	// use Propel\Runtime\Collection\ObjectCollection;
// Dplus Models
use PhoneBookQuery as Query;
use PhoneBook as Record;

/**
 * AbstractType
 * Template Class for querying phoneadr records from database
 */
class AbstractType extends PhoneBook {
	const TYPE = '';

	protected static $instance;

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Query Filtered by type
	 * @return Query
	 */
	public function queryType() {
		return $this->query()->filterByType(static::TYPE);
	}
}