<?php namespace Controllers\Ajax\Json;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus CRUD
use Dplus\Mpm\Pmmain\Bmm;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Mpm extends AbstractController {
	public static function test() {
		return 'test';
	}

	public static function validateBomid($data) {
		$fields = ['bomID|text', 'jqv|bool'];
		self::sanitizeParametersShort($data, $fields);
		$bmm = new Bmm();

		$exists = $bmm->header->exists($data->bomID);

		if (boolval($data->jqv) === false) {
			return $exists;
		}

		if ($exists === false) {
			return "BoM $data->bomID not found";
		}
		return true;
	}

	public static function validateBomComponent($data) {
		$fields = ['bomID|text', 'component|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		if ($data->bomID == $data->component) {
			return boolval($data->jqv) ? "Component cannot be the same as Finished Good Item ID" : false;
		}

		$validate = new Validators\Min();

		if ($validate->itemid($data->component) === false) {
			return boolval($data->jqv) ? "Component Item ID not found" : false;
		}
		return true;
	}
}
