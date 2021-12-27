<?php namespace Dplus\Configs;
// Propel ORM Library
use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Model;
// Dplus Models
use ConfigSysdQuery, ConfigSysd;

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

	/**
	 * Return Config from Database
	 * @return Model
	 */
	public static function getConfig() {
		$q = static::query();
		return $q->findOneByCompanynbr(self::pw('config')->companynbr);
	}
}
