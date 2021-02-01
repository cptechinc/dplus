<?php namespace Controllers\Ajax\Json;

use ProcessWire\Module, ProcessWire\ProcessWire;

use Mvc\Controllers\AbstractController;

use CustomerQuery, Customer;

class Mci extends AbstractController {
	public static function test() {
		return 'test';
	}

	public static function validateCustid($data) {
		$fields = ['custID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		if (CustomerQuery::create()->filterByCustid($data->custID)->count() === 0) {
			return "Customer $data->custID not found";
		}
		return true;
	}
}
