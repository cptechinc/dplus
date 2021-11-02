<?php namespace Dplus\Configs;

use ConfigGlQuery, ConfigGl;

use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Model;

/**
 * ConfigGl
 * Class for getting AP config
 */
class Gl extends AbstractConfig {
	const MODEL = 'ConfigGl';

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
	 * Return Company ID from GL Config
	 * @return string
	 */
	public static function companyid() {
		$class = self::MODEL;
		$q = self::query();
		$q->select($class::aliasproperty('companyid'));
		return $q->findOne();
	}
}
