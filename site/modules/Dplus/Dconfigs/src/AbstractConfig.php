<?php namespace Dplus\Configs;


Abstract class AbstractConfig {
	const MODEL = 'ConfigCi';

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
	 * @return ConfigCi
	 */
	public static function config() {
		return $this->query()->findOne();
	}

	/**
	 * Return Query Class Name
	 * @return string
	 */
	public static function queryClassName() {
		return static::MODEL.'Query';
	}
}
