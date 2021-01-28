<?php namespace Mvc\Controllers;

use ProcessWire\ProcessWire;
use ProcessWire\Sanitizer;
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Session;
use ProcessWire\User;


abstract class AbstractController extends WireData {
	/**
	 * Return the current ProcessWire Wire Instance
	 * @return ProcessWire
	 */
	public static function pw($var = '') {
		$wire = ProcessWire::getCurrentInstance();
		return $var ? $wire->wire($var) : $wire;
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
}
