<?php namespace Pauldro\ProcessWire\Logs;
// Pauldro ProcessWire
use Pauldro\ProcessWire\DatabaseTable\AbstractDatabaseTable;
use Pauldro\ProcessWire\DatabaseTable\Model;

/**
 * AbstractTableLog
 * Logs Data into Database Table
 */
abstract class AbstractTableLog extends AbstractDatabaseTable {
	const TABLE = '';
	const FORMAT_DATETIME = 'Y-m-d H:i:s';
	const COLUMNS = [];
	const PRIMARYKEY = [];
	const MODEL_CLASS = '\\Pauldro\\ProcessWire\\DatabaseTable\\Model';

/* =============================================================
	CRUD Reads
============================================================= */
	/**
	 * Return Records
	 * @param  int    $page  Page Number
	 * @param  int    $limit Number of Records to return
	 * @return array[Model]
	 */
	public function findPaged($page = 1, $limit = 10) {
		$offset = $page == 1 ? 0 : ($page - 1) * $limit;
		$q = $this->query();
		$q->select('*');
		$q->limit("$offset, $limit");
		return $q->execute()->fetchAll(static::MODEL_CLASS);
	}

	/**
	 * Return Records
	 * @param  int    $page  Page Number
	 * @param  int    $limit Number of Records to return
	 * @return array[Model]
	 */
	public function findNewestRecordsPaged($page = 1, $limit = 10) {
		$offset = $page == 1 ? 0 : ($page - 1) * $limit;
		$q = $this->query();
		$q->select('*');
		$q->limit("$offset, $limit");
		$q->orderby('id DESC');
		return $q->execute()->fetchAll(static::MODEL_CLASS);
	}

	/**
	 * Return if Record Exists
	 * @param  string $id
	 * @return bool
	 */
	public function exists($id) {
		$q = $this->query();
		$q->select('COUNT(*)');
		$q->where('id=:id', [':id' => $id]);
		return boolval($q->execute()->fetchColumn());
	}

	/**
	 * Return Record
	 * @param  string $id Record ID
	 * @return Model
	 */
	public function record($id) {
		$q = $this->query();
		$q->select('*');
		$q->where('id=:id', [':id' => $id]);
		return $q->execute()->fetch(static::MODEL_CLASS);
	}
}
