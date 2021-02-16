<?php namespace Mvc;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

use ProcessWire\Page;
use ProcessWire\WireData;
use ProcessWire\Wire404Exception;

/**
 * Router
 */
class JsonRouter extends Router {
	/**
	 * Call the Handler Function
	 * @param  array $routeInfo
	 * @return mixed
	 */
	public function handle($routeInfo) {
		if ($this->exists($routeInfo) === false) {
			return [
				'error' => true,
				'code'  => 404,
				'route' => $routeInfo
			];
		}

		$handler = $routeInfo[1];
		$class = $handler[0];
		if (class_exists($class) == false) {
			return [
				'error' => true,
				'code'  => 404,
				'msg'   => "$class does not exist"
			];
		}
		$methodName = strtoupper($handler[1]);
		if (method_exists($class, $methodName) === false) {
			return [
				'error' => true,
				'code'  => 404,
				'msg'   => "Class Method $class::$methodName does not exist"
			];
		}
		$vars = (object) $routeInfo[2];
		$vars = array_merge((array) $this->params(), (array) $vars);
		// convert array to object:
		$vars = json_decode(json_encode($vars));
		return $class::$methodName($vars);
	}
}
