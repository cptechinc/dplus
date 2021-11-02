<?php namespace Controllers\Ajax\Json;
// Dplus CRUD
use Dplus\Codes\Mpr as MprCodes;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Mpr extends AbstractController {
	public static function test() {
		return 'test';
	}

	public static function validateSourceExists($data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$src = MprCodes\Src::getInstance();
		$exists = $src->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "Source $data->code already exists";
		}

		if ($exists === false) {
			return "Source $data->code not found";
		}
		return true;
	}

	public static function getSource($data) {
		self::sanitizeParametersShort($data, ['code|text']);

		$src = MprCodes\Src::getInstance();
		if ($src->exists($data->code) === false) {
			return false;
		}
		$code = $src->code($data->code);
		$response = [
			'code'         => $code->code,
			'description'  => $code->description,
		];
		return $response;
	}
}
