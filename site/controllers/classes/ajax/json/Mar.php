<?php namespace Controllers\Ajax\Json;
// ProcessWire Mlasses, Modules
use ProcessWire\Module, ProcessWire\ProcessWire;
// Dplus Validators
use Dplus\CodeValidators\Mar       as MarValidator;
use Dplus\CodeValidators\Map\Vxm   as VxmValidator;
use Dplus\CodeValidators\Map\Mxrfe as MxrfeValidator;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Mar extends AbstractController {
	public static function test() {
		return 'test';
	}

	public static function validateSalesPersonId($data) {
		$valid = false;
		$fields = ['id|text', 'jqv|bool', 'new|bool'];
		$data = self::sanitizeParametersShort($data, $fields);
		$valid = self::_validateSalesPersonId($data);

		if ($data->new) {
			$canuse = $valid === false;

			if ($canuse === false && $data->jqv) {
				return "Sales Person $data->id already exists";
			}
			return $canuse;
		}

		if ($data->jqv && $valid === false) {
			return "Sales Person $data->id not found";
		}
		return $valid;
	}

	public static function _validateSalesPersonId($data) {
		$exists = false;
		$fields = ['id|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = new MarValidator();

		if ($validate->salespersonid($data->id) === false) {
			return false;
		}
		return true;
	}

	public static function validateSalesGroupId($data) {
		$valid = false;
		$fields = ['id|text', 'jqv|bool', 'new|bool'];
		$data = self::sanitizeParametersShort($data, $fields);

		$valid = self::_validateSalesGroupId($data);

		if ($data->new) {
			$canuse = $valid === false;
			if ($canuse === false && $data->jqv) {
				return "Sales Person $data->id already exists";
			}
			return $canuse;
		}

		if ($valid === false && $data->jqv) {
			return "Sales Group $data->id not found";
		}
		return $valid;
	}

	public static function _validateSalesGroupId($data) {
		$exists = false;
		$fields = ['id|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = new MarValidator();

		if ($validate->salesgroupid($data->id) === false) {
			return false;
		}
		return true;
	}
}
