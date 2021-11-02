<?php namespace Controllers\Routers;

use ProcessWire\WireData;

use Controllers\Routers;

/**
 * Factory Class that finds a class Responsible for routing Function codes
 */
class Factory extends WireData {
	private $router = null;

	const ROUTERS = [
		Routers\Mpo::class,
		Routers\Min::class,
		Routers\Mpm::class,
		Routers\Mpr::class,
		Routers\Mgl::class,
	];

	/**
	 * Return if Route exists for Function code
	 * @param  string $function Function code
	 * @return bool
	 */
	public function exists($function = '') {
		foreach (self::ROUTERS as $router) {
			if ($router::exists($function)) {
				$this->router = $router;
				return true;
			}
		}
		return false;
	}

	/**
	 * Redirect to Function Location
	 * @param  string $function Function code
	 * @return bool
	 */
	public function route($function) {
		if (empty($this->router) === false) {
			if ($this->router::exists($function)) {
				$this->router::route($function);
			}
		}

		foreach (self::ROUTERS as $router) {
			if ($router::exists($function)) {
				$router::route($function);
			}
		}
		return false;
	}
}
