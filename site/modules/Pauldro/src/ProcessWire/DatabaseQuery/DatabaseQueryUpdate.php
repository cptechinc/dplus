<?php namespace Pauldro\ProcessWire\DatabaseQuery;
// ProcessWire
use ProcessWire\DatabaseQuery;

/**
 * DatabaseQueryUpdate
 *
 * A wrapper for Update SQL queries.
 * 
 * @property array  $UpdateInto
 * @property array  $columns
 * @property array  $values
 * @property string $comment Comments for query
 * 
 * @method $this UpdateInto($sql)
 * @method $this columns(array $columns = array)
 * @method $this values($sql, array $params = array())
 */
class DatabaseQueryUpdate extends DatabaseQuery {

	/**
	 * DB cache setting from $config
	 * 
	 * @var null
	 * 
	 */
	static $dbCache = null;

	/**
	 * Setup the components of a Update query
	 *
	 */
	public function __construct() {
		parent::__construct();
		$this->addQueryMethod('update', 'UPDATE ', ', ');
		$this->addQueryMethod('setValues', ' SET ', ', ', '');

		$this->set('comment', ''); 
	}

	/**
	 * Return the resulting SQL ready for execution with the database
 	 *
	 */
	public function getQuery() {

		$sql = trim(	
			$this->getQueryMethod('update') . 
			$this->getQueryMethod('setValues') .
			$this->getQueryMethod('where')
		) . ' ';

		if($this->get('comment') && $this->wire('config')->debug) {
			// NOTE: PDO thinks ? and :str param identifiers in /* comments */ are real params
			// so we str_replace them out of the comment, and only support comments in debug mode
			$comment = str_replace(array('*/', '?', ':'), '', $this->comment); 
			$sql .= "/* $comment */";
		}

		return $sql; 
	}
	 

}

