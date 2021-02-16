<?php namespace Mvc\Whoops\Handlers;

use Whoops\Handler\JsonResponseHandler as WhoopsHandler;

/**
 * Json Handler
 * Wrappper Class to hold WhoopsHandler Statically so data
 * could be added to the Handler
 */
class JsonHandler {
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
