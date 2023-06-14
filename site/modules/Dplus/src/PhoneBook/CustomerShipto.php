<?php namespace Dplus\PhoneBook;
// Propel Classes
	// use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
	// use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Record;
	// use Propel\Runtime\Collection\ObjectCollection;
// Dplus Models
use PhoneBookQuery as Query;
use PhoneBook as Record;

/**
 * Customer Shipto
 * Template Class for querying phoneadr records from database
 */
class CustomerShipto extends AbstractType {
	const TYPE = 'CS';

	protected static $instance;

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Query filtered for Customer ID, Shipto ID
	 * @param string $custID
	 * @param string $shiptoID
	 * @return Query
	 */
	public function queryCustidShiptoid($custID, $shiptoID) {
		return $this->queryType()->filterByKey1($custID)->filterByKey2($shiptoID);
	}

/* =============================================================
	Read Functions
============================================================= */	
	/**
	 * Return the number of contacts for Customer
	 * @param  string $custID
	 * @param  string $shiptoID
	 * @return int
	 */
	public function countByCustid($custID, $shiptoID) {
		return $this->queryCustidShiptoid($custID, $shiptoID)->count();
	}

	/**
	 * Return the first record that matches
	 * @param  string $custID
	 * @param string $shiptoID
	 * @param  string $contactID
	 * @return Record
	 */
	public function findOne($custID, $shiptoID, $contactID = '') {
		$q = $this->queryCustidShiptoid($custID, $shiptoID);
		if ($contactID) {
			$q->filterByContactid($contactID);
		}
		return $q->findOne();
	}
}