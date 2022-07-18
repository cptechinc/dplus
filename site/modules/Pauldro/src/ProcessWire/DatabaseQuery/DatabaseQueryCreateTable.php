<?php namespace Pauldro\ProcessWire\DatabaseQuery;
// ProcessWire
use ProcessWire\DatabaseQuery;

/**
 * ProcessWire DatabaseQueryCreateTable
 *
 * A wrapper for CREATE TABLE SQL queries.
 * 
 * @property array  $create
 * @property array  $columns
 * @property array  $primary
 * @property string $comment Comments for query
 * 
 * @method $this create($sql)
 * @method $this columns(array $columns)
 * @method $this primarykey(array $primarykey)
 */
class DatabaseQueryCreateTable extends DatabaseQuery {
	/**
	 * Setup the components of a Create Table query
	 */
	public function __construct() {
		parent::__construct();
		$this->addQueryMethod('table', ' CREATE TABLE `', '', '`');
		$this->addQueryMethod('columns', ' (', ', ', ')');
		$this->addQueryMethod('primarykey', ' PRIMARY KEY (`', '`, `', '`)');
		$this->set('comment', ''); 
	}

	/**
	 * Return the resulting SQL ready for execution with the database
	 */
	public function getQuery() {
		$sql  = trim($this->getQueryMethod('table'));
		$sql .= " (" . PHP_EOL;
		$sql .= trim($this->getQueryMethod('columns'));
		$sql .= "," . PHP_EOL;
		$sql .= trim($this->getQueryMethod('primarykey'));
		$sql .= PHP_EOL . ");";

		if($this->get('comment') && $this->wire('config')->debug) {
			// NOTE: PDO thinks ? and :str param identifiers in /* comments */ are real params
			// so we str_replace them out of the comment, and only support comments in debug mode
			$comment = str_replace(array('*/', '?', ':'), '', $this->comment); 
			$sql .= "/* $comment */";
		}
		return $sql; 
	}

	/**
	 * Get columns section of SQL
	 * @return string
	 */
	protected function getQueryColumns() {
		if(!count($this->columns)) return '';
		$sql = "";

		$cols = [];
		
		foreach ($this->columns as $name => $attr) {
			$column = array_merge(["`$name`"], $attr);
			$cols[] = implode(' ', $column);
		}
		$sql .= implode(", \n", $cols);
		return $sql;
	}
}

