<?php namespace Pauldro\ProcessWire\Caches;
// Pauldro ProcessWire
use Pauldro\ProcessWire\DatabaseTable\AbstractDatabaseTable;

/**
 * AbstractCacheTable
 * Provides Cache functionality via database table
 */
abstract class AbstractCacheTable extends AbstractDatabaseTable {
	const TABLE = '';
	const FORMAT_DATETIME = 'Y-m-d H:i:s';
	const COLUMNS = [];
	const PRIMARYKEY = [];
	const MODEL_CLASS = '\\Pauldro\\ProcessWire\\DatabaseTable\\Model';

/* =============================================================
	CRUD Reads
============================================================= */
	

/* =============================================================
	CRUD Creates
============================================================= */
	

/* =============================================================
	Source Data Functions
============================================================= */
	/**
	 * Import One Record 
	 * @param  array $record ['itemid' => $itemid, 'model' => $model, 'year' => $year]
	 * @return void
	 */
	public function importArray(array $record) {
		if ($this->validateArrayKeys($record) === false) {
			return false;
		}
		if ($this->existsArray($record)) {
			return true;
		}
		return $this->insertArray($record);
	}
}