<?php namespace Controllers\Ajax\Json;
// ProcessWire
use ProcessWire\WireData;
// Dplus Validators
use Dplus\CodeValidators as Validators;
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

	public static function validatePrinter($data) {
		$fields = ['id|text', 'strict|bool', 'jqv|bool'];
		self::sanitizeParametersShort($data, $fields);
		$validator = new Validators\Printer();

		if ($data->jqv) { // JQueryValidate
			if ($data->strict) {
				return $validator->id($data->id) ? true : "Printer $data->id not found";
			}
			return $validator->printer($data->id) ? true : "Printer & Pitch $data->id not found";
		}

		if ($data->strict) {
			return $validator->id($data->id);
		}
		return $validator->printer($data->id);
	}
}
