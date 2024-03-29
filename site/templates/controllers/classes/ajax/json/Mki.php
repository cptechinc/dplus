<?php namespace Controllers\Ajax\Json;
// Dplus Model
use InvKitQuery, InvKit;
// ProcessWire Classes, Modules
use ProcessWire\Module, ProcessWire\ProcessWire;
// Dplus Validators
use Dplus\CodeValidators\Mki\Kim as KimValidator;
// Mvc Controllers
use Mvc\Controllers\Controller;

class Mki extends Controller {
	public static function test() {
		return 'test';
	}

	public static function validateKitid($data) {
		$fields = ['kitID|string'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		if ($validate->kit($data->kitID) === false) {
			return "Kit $data->kitID not found";
		}
		return true;
	}

	public static function validateKitidNew($data) {
		$exists = self::validateKitid($data);
		if ($exists === true) {
			return "Kit $data->kitID exists";
		}
		return true;
	}

	public static function validateKitDeletion($data) {
		$fields = ['kitID|string'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		if ($validate->can_delete($data->kitID) === false) {
			return "Cannot delete Kit ID $data->kitID. It has committed orders.";
		}
		return true;
	}

	public static function getKit($data) {
		$fields = ['kitID|string'];
		$data     = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		if ($validate->kit($data->kitID) === false) {
			return false;
		}
		$kit = InvKitQuery::create()->findOneByItemid($data->kitID);
		$response = array(
			'kitid'       => $data->kitID,
			'description' => $kit->item->description
		);
	}

	public static function validateKitComponent($data) {
		$fields = ['kitID|string', 'component|string'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		if ($validate->kit_component($data->kitID, $data->component) === false) {
			return "Kit $data->kitID Component $data->component not found";
		}
		return true;
	}

	private static function validator() {
		return new KimValidator();
	}
}
