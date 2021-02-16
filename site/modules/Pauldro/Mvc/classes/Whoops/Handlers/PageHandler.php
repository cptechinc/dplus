<?php namespace Mvc\Whoops\Handlers;

use Whoops\Handler\PrettyPageHandler as WhoopsHandler;

/**
 * Page Handler
 * Wrappper Class to hold WhoopsHandler Statically so data
 * could be added to the Handler
 */
class PageHandler {
	private static $handler;

	/**
	 * Return the Handler
	 * @return WhoopsHandler
	 */
	public static function handler() {
		if (empty(self::$handler)) {
			self::$handler = new WhoopsHandler();
		}
		return self::$handler;
	}
}
