<?php namespace Dplus\Configs;

use ConfigIiQuery, ConfigIi;

use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Model;

/**
 * ConfigIi
 * Class for getting II config
 */
class Ii extends AbstractConfig {
	const MODEL = 'ConfigIi';

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
