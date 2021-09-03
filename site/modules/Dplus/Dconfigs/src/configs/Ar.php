<?php namespace Dplus\Configs;

use ConfigArQuery, ConfigAr;

use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Model;

/**
 * ConfigAr
 * Class for getting AR config
 */
class Ar extends AbstractConfig {
	const MODEL = 'ConfigAr';

	/** @var Model */
	protected static $config;

	/**
	 * Return Config from Memory
	 * @return Model
	 */
	public static function config() {
		if (empty(static::$config)) {
			static::$config = static::getConfig();
		}
		return static::$config;
	}
}
