<?php namespace Mvc\Routers;
// PHP Core
use Exception;
// Whoops
use Whoops\Run as Whoops;
// FastRoute Routing Library
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
// ProcessWire
use ProcessWire\ProcessWire;
use ProcessWire\WireData;
use ProcessWire\Wire404Exception;
// MVC Whoops
use Mvc\Whoops\Handlers\Page as PageHandler;
use Mvc\Whoops\Handlers\Production as ProductionHandler;
use Mvc\Whoops\Handlers\EmailPage as EmailPageHandler;

/**
 * Router
 * @property array  $routes      Array of Routes
 * @property string $path        Path to Begin Routing from
 * @property array  $routeInfo   Route Information from Dispatcher
 * @property array  $routeprefix Path to Begin Routing from
 *
 */
class Router extends WireData {
	protected $routes = [];
	protected $error = false;

	public function __construct() {
		$this->routes = [];
		$this->path = '';
		$this->routeInfo = [];
		$this->routeprefix = '';
	}

	/**
	 * Return if Router has Error
	 * @return bool
	 */
	public function hasError() {
		return $this->error;
	}

	/**
	 * Set if Router has Error
	 * @param bool $error
	 */
	public function setError($error = false) {
		$this->error = $error;
	}

	/**
	 * Set Routes to route for
	 * @param  array $routes
	 * @return void
	 */
	public function setRoutes($routes = []) {
		$this->routes = $routes;
	}

	/**
	 * Set Routes to route for
	 * @param  array $routes
	 * @return void
	 */
	public function setRoutePrefix($prefix = '') {
		$this->routeprefix = $prefix;
	}

	/**
	 * Return Route Handler Call
	 * @return mixed
	 */
	public function route() {
		$input = $this->wire('input');
		$dispatcher = $this->dispatcher();
		$this->routeInfo  = $dispatcher->dispatch($input->requestMethod(), $input->url());

		if (array_key_exists(2, $this->routeInfo)) {
			$params = $this->routeInfo[2];
			if (array_key_exists('pagenbr', $params)) {
				$input->setPageNum(intval($params['pagenbr']));
			}
		}
		return $this->handleRoute($this->routeInfo);
	}

	/**
	 * Try Calling the Handler Function, catch errors if needed
	 * @param  array $routeInfo
	 * @return strings
	 */
	protected function handleRoute() {
		$response = '';

		try {
			$response = $this->handle($this->routeInfo);
		} catch (Wire404Exception $e) {
			$this->error = true;
			throw $e;
		} catch (Exception $e) {
			$this->error = true;
			$response = $this->handleException($e);
		}
		return $response;
	}

	/**
	 * Return Response after Handling Exception
	 * @param  Exception $e
	 * @return string
	 */
	protected function handleException(Exception $e) {
		$whoops = new Whoops();
		$whoops->allowQuit(false);
		$whoops->writeToOutput(false);

		$whoops->pushHandler($this->getWhoopsEmailPageHandler());

		if ($this->wire('config')->debug === true) {
			$whoops->pushHandler($this->getWhoopsPageHandler());
			return $whoops->handleException($e);
		}
		$whoops->handleException($e);
		$pw = ProcessWire::getCurrentInstance();
		return $pw->wire('config')->twig->render('util/alert.twig', ['type' => 'danger', 'iconclass' => 'fa fa-warning fa-2x', 'title' =>'Error!', 'message' => 'An Error has Occurred, support has been emailed']);
	}

	/**
	 * @return PageHandler
	 */
	protected function getWhoopsPageHandler() {
		$handler = new PageHandler();
		$this->addAppDataTablesToHandler($handler);
		return $handler;
	}

	/**
	 * @return EmailPageHandler
	 */
	protected function getWhoopsEmailPageHandler() {
		$handler = new EmailPageHandler();
		$this->addAppDataTablesToHandler($handler);
		return $handler;
	}

	/**
	 * Add Data Tables for App
	 * @param PageHandler $handler
	 * @return void
	 */
	protected function addAppDataTablesToHandler(PageHandler $handler) {
		$handler->addDataTable('App', [
			'User ID'    => $this->wire('user')->loginid,
			'Session ID' => session_id(),
			'Path'       => $this->wire('input')->url(),
		]);
	}

	/**
	 * Return if Route exists
	 * @param  array $routeInfo
	 * @return bool
	 */
	public function exists($routeInfo) {
		switch ($routeInfo[0]) {
			case Dispatcher::NOT_FOUND:
				return false;
				break;
			case Dispatcher::METHOD_NOT_ALLOWED:
				return false;
				break;
			case Dispatcher::FOUND:
				return true;
				break;
		}
	}

	/**
	 * Call the Handler Function
	 * @param  array $routeInfo
	 * @return mixed
	 */
	public function handle($routeInfo) {
		if ($this->exists($routeInfo) === false) {
			throw new Wire404Exception();
		}

		$handler = $routeInfo[1];
		$class = $handler[0];

		if (class_exists($class) == false) {
			throw new Exception("Class $class does not exist");
		}

		$methodName = strtoupper($handler[1]);

		if (method_exists($class, $methodName) === false) {
			throw new Exception("Class Method $class::$methodName does not exist");
		}

		$vars = (object) $routeInfo[2];
		$vars = array_merge((array) $this->params(), (array) $vars);

		// convert array to object:
		// $vars = json_decode(json_encode($vars));
		$data = new WireData();
		$data->setArray($vars);
		return $class::$methodName($data);
	}

	public function params($index = null, $default = null, $source = null) {
		// check for php://input and merge with $_REQUEST
		if ((isset($_SERVER['CONTENT_TYPE']) &&
			stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) ||
			(isset($_SERVER['HTTP_CONTENT_TYPE']) &&
		stripos($_SERVER['HTTP_CONTENT_TYPE'], 'application/json') !== false) // PHP build in Webserver !?
		) {
			if ($json = json_decode(@file_get_contents('php://input'), true)) {
				$_REQUEST = array_merge($_REQUEST, $json);
			}
		}

		$src = $source ? $source : $_REQUEST;

		return $this->fetch_from_array($src, $index, $default);
	}

	public function fetch_from_array(&$array, $index = null, $default = null) {
		if (is_null($index)) {
			return $array;
		} elseif (isset($array[$index])) {
			return $array[$index];
		} elseif (strpos($index, '/')) {
			$keys = explode('/', $index);

			switch (count($keys)) {
				case 1:
				if (isset($array[$keys[0]])) {
					return $array[$keys[0]];
				}
				break;

				case 2:
				if (isset($array[$keys[0]][$keys[1]])) {
					return $array[$keys[0]][$keys[1]];
				}
				break;

				case 3:
				if (isset($array[$keys[0]][$keys[1]][$keys[2]])) {
					return $array[$keys[0]][$keys[1]][$keys[2]];
				}
				break;

				case 4:
				if (isset($array[$keys[0]][$keys[1]][$keys[2]][$keys[3]])) {
					return $array[$keys[0]][$keys[1]][$keys[2]][$keys[3]];
				}
				break;
			}
		}

		return $default;
	}

	protected static function flattenRoutes(&$putInArray, $group, $prefix = '') {
		$prefix = rtrim($prefix, '/');

		foreach ($group as $key => $item) {
			// Check first item in item array to see if it is also an array
			if (is_array(reset($item))) {
				self::flattenRoutes($putInArray, $item, $prefix . '/' . $key);
			} else {
				$item[1] = $prefix . '/' . $item[1];
				array_push($putInArray, $item);
			}
		}
	}

	/**
	 * Return RouteCollector
	 * @return RouteCollector
	 */
	public function router() {
		$routes = $this->routes;

		$flatroutes = [];
		self::flattenRoutes($flatroutes, $routes, $this->routeprefix);

		// create FastRoute Dispatcher:
		$router = function (RouteCollector $r) use ($flatroutes) {
			foreach ($flatroutes as $key => $route) {
				if (!is_array($route)) {
					continue;
				}
				$method = $route[0];
				$url    = $route[1];

				// add trailing slash if not present:
				if (substr($url, -1) !== '/') {
					$url .= '/';
				}

				$class       = isset($route[2]) ? $route[2] : false;
				$function    = isset($route[3]) ? $route[3] : false;
				$routeParams = isset($route[4]) ? $route[4] : [];

				$r->addRoute($method, $url, [$class, $function, $routeParams]);
			}
		};
		return $router;
	}

	/**
	 * Return Dispatcher
	 * @return FastRoute\Dispatcher;
	 */
	public function dispatcher() {
		$router = $this->router();
		return \FastRoute\simpleDispatcher($router);
	}
}
