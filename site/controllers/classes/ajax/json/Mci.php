<?php namespace Controllers\Ajax\Json;

use ProcessWire\Module, ProcessWire\ProcessWire;

use Mvc\Controllers\AbstractController;

use Dplus\CodeValidators\Mar as MarValidator;

class Mci extends AbstractController {
	public static function test() {
		return 'test';
	}

	public static function validateCustid($data) {
		$fields = ['custID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		if ($validate->custid($data->custID) === false) {
			return "Customer $data->custID not found";
		}
		return true;
	}

	private static function validator() {
		return new MarValidator();
	}
}
