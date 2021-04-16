<?php namespace Dplus\Configs;

use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Model;


abstract class AbstractConfig {
	const MODEL = '';

	/**
	 * Return New Query Class
	 * @return Query
	 */
	public static function query() {
		$class = self::queryClassName();
		return $class::create();
	}

	/**
	 * Return Config Record
	 * @return Model
	 */
	public static function config() {
		return static::query()->findOne();
	}

	/**
	 * Return Query Class Name
	 * @return string
	 */
	public static function queryClassName() {
		return static::MODEL.'Query';
	}
}
