<?php namespace Dplus\Configs;

use Dplus\Configs\AbstractConfig;

use ConfigCiQuery, ConfigCi;

use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Model;

/**
 * ConfigCi
 * Class for getting CI config
 */
class Ci extends AbstractConfig {
	const MODEL = 'ConfigCi';

	/**
	 * Return New Query Class
	 * @return Query
	 */
	public static function query() {
		$class = static::queryClassName();
		return $class::create();
	}

	/**
	 * Return Config Record
	 * @return Model
	 */
	public static function config() {
		return self::query()->findOne();
	}

	/**
	 * Return Query Class Name
	 * @return string
	 */
	public static function queryClassName() {
		return static::MODEL.'Query';
	}
}
