<?php namespace Pauldro\ProcessWire\DatabaseQuery;
// ProcessWire
use ProcessWire\DatabaseQuery;

/**
 * DatabaseQueryInsert
 *
 * A wrapper for INSERT SQL queries.
 * 
 * @property array  $insertInto
 * @property array  $columns
 * @property array  $values
 * @property string $comment Comments for query
 * 
 * @method $this insertInto($sql)
 * @method $this columns(array $columns = array)
 * @method $this values($sql, array $params = array())
 */
class DatabaseQueryInsert extends DatabaseQuery {
	/**
	 * Setup the components of an Insert query
	 */
	public function __construct() {
		parent::__construct();
		$this->addQueryMethod('insertInto', 'INSERT INTO ', ', ');
		$this->addQueryMethod('columns', ' (', ', ', ')');
		$this->addQueryMethod('values', ' VALUES (', ', ', ')');
		$this->set('comment', ''); 
	}

	/**
	 * Return the resulting SQL ready for execution with the database
	 */
	public function getQuery() {
		$sql = trim(	
			$this->getQueryMethod('insertInto') . 
			$this->getQueryMethod('columns') .
			$this->getQueryMethod('values')
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

