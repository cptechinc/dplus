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
	/**
	 * Return the number of contacts for Customer
	 * @param  string $custID
	 * @return int
	 */
	public function countByCustid($custID) {
		return $this->queryCustid($custID)->count();
	}

	/**
	 * Return the first record that matches
	 * @param  string $custID
	 * @param  string $contactID
	 * @return Record
	 */
	public function findOne($custID, $contactID = '') {
		$q = $this->queryCustid($custID);
		if ($contactID) {
			$q->filterByContactid($contactID);
		}
		return $q->findOne();
	}
}