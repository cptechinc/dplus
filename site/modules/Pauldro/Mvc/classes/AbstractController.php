<?php namespace Mvc\Controllers;

use ProcessWire\ProcessWire;
use ProcessWire\Sanitizer;
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Session;
use ProcessWire\User;


abstract class AbstractController extends WireData {
	private static $pw;

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
		foreach ($fields as $name => $field) {
				// Check if Param exists
			if (!isset($data->$name)) {
				$data->$name = false;
				continue;
			}

			$method = $field['sanitizer'];
			$data->$name = self::sanitizeByMethod($data->$name, $method);
		}

		return $data;
	}

	public static function sanitizeParametersShort($data, $fields) {
		foreach ($fields as $param) {
			// Split param: Format is name|sanitizer method
			$arr = explode('|', $param);
			$name = $arr[0];
			$method = $arr[1];

				// Check if Param exists
			if (!isset($data->$name)) {
				$data->$name = '';
				continue;
			}
			$data->$name = self::sanitizeByMethod($data->$name, $method);
		}
		return $data;
	}

	public static function sanitizeByMethod($subject, $method) {
		$sanitizer = self::pw('sanitizer');
		// Sanitize Data
		// If no sanitizer is defined, use the text sanitizer as default
		$method = $method ? $method : 'text';

		if (!method_exists($sanitizer, $method) && $sanitizer->hooks->isHooked("Sanitizer::$method()") === false) {
			$method = 'text';
		}
		return $sanitizer->$method($subject);
	}
}
