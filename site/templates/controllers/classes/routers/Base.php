<?php namespace Controllers\Routers;

// Mvc Controllers
use Mvc\Controllers\AbstractController;
use Controllers\Mpo\Poadmn;

class Base extends AbstractController {
	const ROUTES = [
		'cnfm' => ['', Poadmn\Cnfm::class, 'cnfmUrl']
	];


	public static function exists($function = '') {
		return array_key_exists($function, self::ROUTES);
	}

	public static function route($function = '') {
		if (static::exists($function) === false) {
			return false;
		}
		$url = static::url($function);
		return self::pw('session')->redirect($url, $http301);
	}

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
			throw new Exception("Class Method $class::$methodName does not exist");
		}
		return $class::$methodName();
	}
}
