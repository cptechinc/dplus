<?php namespace ProcessWire;

/**
 * Functions that parse attributes for html
 */
trait AttributeParser {
	/**
	 * Takes a string of attributes and parses it out by a delimiter (|)
	 * @param  string $vars string of attributes separated by |
	 * @return string       string of atrributes and values like class=""
	 */
	protected function attributes($vars) {
		$attributesarray = array();
		$attributes = '';

		if (!empty($vars)) {
			$values = explode('|', $vars);
			foreach ($values as $value) {
				$pieces = explode('=', $value);
				if (!empty($pieces[0])) {
					$attributesarray[array_shift($pieces)] = implode('=', $pieces);
				}

			}
		}

		if (!empty($attributesarray)) {
			foreach ($attributesarray as $key => $value) {
				if ($value == 'noparam') {
					$attributes .= ' ' . $key;
				} else {
					$attributes .= ' ' . $key . '="' . $value . '"';
				}
			}
		}
		return $attributes;
	}

	/**
	 * Takes a string of attributes and parses it out by a delimiter (|)
	 * @param  string $attributes string of attributes separated by |
	 * @return string             string of atrributes and values like class=""
	 * @uses attributes()
	 */
	public function generate_attributes($attributes) {
		return $this->attributes($attributes);
	}

	/**
	 * Throws an error to be logged
	 * @param  string $error Description of Error
	 * @param  int    $level What PHP Error Level
	 * Error constants can be found at
	 * http://php.net/manual/en/errorfunc.constants.php
	 */
	public function error($error, $level = E_USER_ERROR) {
		$trace = debug_backtrace();
		$caller = next($trace);
		$class = get_class($this);
		$error = (strpos($error, "DPLUS [$class]: ") !== 0 ? "DPLUS [$class]: " . $error : $error);
		$error .= PHP_EOL;
		$error .= PHP_EOL;

		if (isset($caller['file'])) {
			$error .= $caller['function'] . " called from " . $caller['file'] . " on line " . $caller['line'];
		} else {
			$error .= "Property may be trying to be loaded from database";
		}
		trigger_error($error, $level);
		return;
	}
}
