<?php namespace Dplus\Configs;

use ConfigSysdQuery, ConfigSysd;

use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Model;

/**
 * ConfigSysd
 * Class for getting Sysd
 */
class Sysd extends AbstractConfig {
	const MODEL = 'ConfigSysd';

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
