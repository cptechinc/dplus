<?php namespace Dplus\PhoneBook;
// Propel Classes
	// use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
	// use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Record;
	// use Propel\Runtime\Collection\ObjectCollection;
// Dplus Models
use PhoneBookQuery as Query;
use PhoneBook as Record;

/**
 * Customer Contact
 * Template Class for querying phoneadr records from database
 */
class CustomerContact extends Customer {
	const TYPE = 'CC';

	protected static $instance;
}