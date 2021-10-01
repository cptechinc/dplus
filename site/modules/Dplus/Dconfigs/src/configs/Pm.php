<?php namespace Dplus\Configs;

use ConfigPmQuery, ConfigPm;

use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Model;

/**
 * ConfigPm
 * Class for getting Pm config
 */
class Pm extends AbstractConfig {
	const MODEL = 'ConfigPm';

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
