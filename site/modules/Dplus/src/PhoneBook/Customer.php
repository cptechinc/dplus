<?php namespace Dplus\PhoneBook;
// Propel Classes
	// use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
	// use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Record;
	// use Propel\Runtime\Collection\ObjectCollection;
// Dplus Models
use PhoneBookQuery as Query;
use PhoneBook as Record;

/**
 * Customer
 * Template Class for querying phoneadr records from database
 */
class Customer extends AbstractType {
	const TYPE = 'C';

	protected static $instance;

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Query filtered for cust ID
	 * @param string $custID
	 * @return Query
	 */
	public function queryCustid($custID) {
		return $this->queryType()->filterByKey1($custID);
	}

/* =============================================================
	Read Functions
============================================================= */	
	public function countByCustid($custID) {
		return $this->queryCustid($custID)->count();
	}
	public function findOne($custID) {
		return $this->queryCustid($custID)->findOne();
	}
}