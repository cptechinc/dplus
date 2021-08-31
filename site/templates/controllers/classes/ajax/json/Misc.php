<?php namespace Controllers\Ajax\Json;

use ProcessWire\WireData;

// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Misc extends AbstractController {
	public static function time($data) {
		$fields = ['format|text'];
		self::sanitizeParametersShort($data, $fields);
		$format = $data->format ? $data->format : 'H:i';
		return date($format);
	}

	public static function date($data) {
		$fields = ['format|text'];
		self::sanitizeParametersShort($data, $fields);
		$format = $data->format ? $data->format : 'm/d/Y';
		return date($format);
	}

	public static function dateTime($data) {
		$fields = ['dateFormat|text', 'timeFormat|text'];
		self::sanitizeParametersShort($data, $fields);
		$date = new WireData();
		$date->format = $data->dateFormat;

		$time = new WireData();
		$time->format = $data->timeFormat;

		$response = [
			'date' => self::date($date),
			'time' => self::time($time),
		];
		return $response;
	}
}
