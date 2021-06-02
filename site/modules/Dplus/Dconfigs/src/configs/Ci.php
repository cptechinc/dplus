<?php namespace Dplus\Configs;

use ConfigCiQuery, ConfigCi;

use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Model;

/**
 * ConfigCi
 * Class for getting CI config
 */
class Ci extends AbstractConfig {
	const MODEL = 'ConfigCi';

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
