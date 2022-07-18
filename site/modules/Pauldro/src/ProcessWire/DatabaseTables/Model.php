<?php namespace Pauldro\ProcessWire\DatabaseTables;
// ProcessWire
use ProcessWire\WireData;

/**
 * Model
 * 
 * Base Class for DatabaseTable Data
 */
class Model extends WireData {
	const PRIMARYKEY = [];
	const GLUE = '|';

	/**
	 * Return Keys for this Model
	 * @return array
	 */
	public function primarykey() {
		$keys = [];

		foreach (static::PRIMARYKEY as $key) {
			$keys[] = $this->$key;
		}
		return $keys;
	}

	/**
	 * Return Primary Key as a string
	 * @return string
	 */
	public function primarykeyString() {
		return implode(static::GLUE, $this->primarykey());
	}
}