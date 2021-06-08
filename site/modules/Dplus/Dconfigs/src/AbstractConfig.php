<?php namespace Dplus\Configs;

use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Model;


abstract class AbstractConfig {
	const MODEL = '';

	/** @var Model */
	protected static $config;

	/**
	 * Return New Query Class
	 * @return Query
	 */
	public static function query() {
		$class = static::queryClassName();
		return $class::create();
	}

	/**
	 * Return Config from Database
	 * @return Model
	 */
	public static function getConfig() {
		return static::query()->findOne();
	}

	/**
	 * Return Config from Memory
	 * @return Model
	 */
	public static function config() {
		return static::getConfig();
	}

	/**
	 * Return Query Class Name
	 * @return string
	 */
	public static function queryClassName() {
		return static::MODEL.'Query';
	}
}
