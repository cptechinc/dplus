<?php namespace Controllers\Routers;
// PHP Core
use Exception;
// Mvc Controllers
use Mvc\Controllers\AbstractController;
use Controllers\Mpo\Poadmn;

abstract class Base extends AbstractController {
	const ROUTES = [
		// '{key}' => ['{url}', '{className}', '{function}']
		'cnfm' => ['', Poadmn\Cnfm::class, 'cnfmUrl']
	];

	/**
	 * Return if Function is Found
	 * @param  string $function Function Code
	 * @return bool
	 */
	public static function exists($function = '') {
		return array_key_exists($function, static::ROUTES);
	}

	/**
	 * Redirect to Function
	 * @uses static::url
	 * @param  string $function Function Code
	 * @return void
	 */
	public static function route($function = '') {
		if (static::exists($function) === false) {
			return false;
		}
		$url = static::url($function);
		return static::pw('session')->redirect($url, $http301);
	}

	/**
	 * Return Url to Function
	 * NOTE: if Route path is defined, use it
	 *
	 * EXAMPLE: ['', Poadmn\Cnfm::class, 'cnfmUrl']
	 * @param  string $function Function Code
	 * @return string
	 */
	protected static function url($function = '') {
		if (static::exists($function) === false) {
			return '';
		}
		$routeData = static::ROUTES[$function];

		if (empty($routeData[0]) === false) {
			return $routeData[0];
		}
		return static::urlFromClass($function);
	}

	/**
	 * Return Url From Route Data using a class function
	 * EXAMPLE: ['', Poadmn\Cnfm::class, 'cnfmUrl']
	 * @param  string $function  Function Code
	 * @return string
	 */
	protected static function urlFromClass($function = '') {
		if (static::exists($function) === false) {
			return '';
		}
		$routeData = static::ROUTES[$function];

		$class = $routeData[1];
		$handler = $routeData[2];

		if (class_exists($class) == false) {
			throw new Exception("Class $class does not exist");
		}

		$methodName = strtoupper($handler);
		if (method_exists($class, $methodName) === false) {
			throw new \Exception("Class Method $class::$methodName does not exist");
		}
		return $class::$methodName();
	}
}
