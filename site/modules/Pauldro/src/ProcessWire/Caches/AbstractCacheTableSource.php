<?php namespace Pauldro\ProcessWire\Caches;
// Pauldro ProcessWire
use ProcessWire\WireData;

/**
 * AbstractCacheTableSource
 * Provides Retreving Data from Source
 */
abstract class AbstractCacheTableSource extends WireData {
	protected static $instance;

	/** @return static */
	public static function instance() {
		if (empty(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * Return Records
	 * @return array
	 */
	abstract public function find();

	/**
	 * Return the number of Total Records
	 * @return int
	 */
	abstract public function count();
}