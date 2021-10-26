<?php namespace Controllers\Ajax\Json;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus CRUD
use Dplus\Mpm\Pmmain\Bmm;
use Dplus\Codes\Mpm\Dcm;
use Dplus\Codes\Mpm\Rcm;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Mpm extends AbstractController {
	public static function test() {
		return 'test';
	}

	public static function validateBomid($data) {
		$fields = ['bomID|text', 'jqv|bool'];
		self::sanitizeParametersShort($data, $fields);
		$dcm = new Bmm();

		$exists = $dcm->header->exists($data->bomID);

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

		$dcm    = new Bmm();
		$exists = $dcm->components->exists($data->bomID, $data->component);

		// Validations for new Components
		if (boolval($data->new) === true) {
			// validate Item ID
			if ($exists === false) {
				$validate = new Validators\Min();

				if ($validate->itemid($data->component) === false) {
					return boolval($data->jqv) ? "Component Item ID not found" : false;
				}
				return boolval($data->jqv) ? true : false;
			}
			// For New components, but it already exists
			return boolval($data->jqv) ? "$data->bomID Component $data->component already exists" : false;
		}
		return $exists;
	}

	public static function validatePrWorkCenterExists($data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$dcm = Dcm::getInstance();
		$exists = $dcm->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "Work Center $data->code already exists";
		}

		if ($exists === false) {
			return "Work Center $data->code not found";
		}
		return true;
	}

	public static function getPrWorkCenter($data) {
		self::sanitizeParametersShort($data, ['code|text']);

		$dcm = Dcm::getInstance();
		if ($dcm->exists($data->code) === false) {
			return false;
		}
		$code = $dcm->code($data->code);
		$response = [
			'code'        => $code->code,
			'description' => $code->description
		];
		return $response;
	}

	public static function validatePrResourceExists($data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$rcm = Rcm::getInstance();
		$exists = $rcm->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "Resource $data->code already exists";
		}

		if ($exists === false) {
			return "Resource $data->code not found";
		}
		return true;
	}

	public static function getPrResource($data) {
		self::sanitizeParametersShort($data, ['code|text']);

		$rcm = Rcm::getInstance();
		if ($rcm->exists($data->code) === false) {
			return false;
		}
		$code = $rcm->code($data->code);
		$response = [
			'code'        => $code->code,
			'description' => $code->description
		];
		return $response;
	}
}
