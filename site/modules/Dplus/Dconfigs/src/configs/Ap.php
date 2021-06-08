<?php namespace Dplus\Configs;

use ConfigApQuery, ConfigAp;

use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Model;

/**
 * ConfigAp
 * Class for getting AP config
 */
class Ap extends AbstractConfig {
	const MODEL = 'ConfigAp';

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
