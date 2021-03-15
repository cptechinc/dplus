<?php namespace Controllers\Ajax\Json;
// ProcessWire Classes, Modules
use ProcessWire\Module, ProcessWire\ProcessWire;
// Dplus Validators
use Dplus\CodeValidators\Mar as MarValidator;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

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
