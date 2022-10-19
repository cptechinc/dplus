<?php namespace Controllers\Ajax\Json;
// ProcessWire Classes, Modules
use ProcessWire\Module, ProcessWire\ProcessWire;
// Dplus Validators
use Dplus\CodeValidators\Mar as MarValidator;
// Mvc Controllers
use Mvc\Controllers\Controller;

class Mci extends Controller {
	public static function test() {
		return 'test';
	}

	public static function validateCustid($data) {
		$fields = ['custID|string', 'jqv|bool'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		if ($validate->custid($data->custID) === false) {
			return $data->jqv ? "Customer $data->custID not found" : false;
		}
		return true;
	}

	private static function validator() {
		return new MarValidator();
	}
}
