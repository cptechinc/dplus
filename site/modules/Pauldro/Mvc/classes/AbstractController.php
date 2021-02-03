<?php namespace Mvc\Controllers;

use ProcessWire\ProcessWire;
use ProcessWire\Sanitizer;
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Session;
use ProcessWire\User;


abstract class AbstractController extends WireData {
	public static $pw;

	/**
	 * Return the current ProcessWire Wire Instance
	 * @param  string            $var   Wire Object 
	 * @return ProcessWire|mixed
	 */
	public static function pw($var = '') {
		if (empty(self::$pw)) {
			self::$pw = ProcessWire::getCurrentInstance();
		}
		return $var ? self::$pw->wire($var) : self::$pw;
	}

	public static function sanitizeParameters($data, $fields) {
		$wire = self::pw();

		foreach ($fields as $name => $field) {
				// Check if Param exists
			if (!isset($data->$name)) {
				$data->$name = false;
				continue;
			}

			$sanitizer = $field['sanitizer'];

			$sanitizer = $sanitizer ? $sanitizer : 'text';

			if (!method_exists($wire->wire('sanitizer'), $sanitizer)) {
				$sanitizer = 'text';
			}

			$data->$name = $wire->wire('sanitizer')->$sanitizer($data->$name);
		}

		return $data;
	}

	public static function sanitizeParametersShort($data, $fields) {
		$wire = self::pw();

		foreach ($fields as $param) {
			// Split param: Format is name|sanitizer
			$arr = explode('|', $param);

			$name = $arr[0];
			$sanitizer = $arr[1];

				// Check if Param exists
			if (!isset($data->$name)) {
				$data->$name = '';
			}

			// Sanitize Data
			// If no sanitizer is defined, use the text sanitizer as default
			$sanitizer = $sanitizer ? $sanitizer : 'text';

			if (!method_exists($wire->wire('sanitizer'), $sanitizer)) {
				$sanitizer = 'text';
			}

			$data->$name = $wire->wire('sanitizer')->$sanitizer($data->$name);
		}
		return $data;
	}
}
