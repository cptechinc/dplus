<?php namespace Controllers\Ajax\Json;

use ProcessWire\Module, ProcessWire\ProcessWire;

use Mvc\Controllers\AbstractController;

use InvKitQuery, InvKit;
use Dplus\CodeValidators\Mki\Kim as KimValidator;

class Mki extends AbstractController {
	public static function test() {
		return 'test';
	}

	public static function validateKitid($data) {
		$fields = ['kitID|text'];
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
		$fields = ['kitID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		if ($validate->can_delete($data->kitID) === false) {
			return "Cannot delete Kit ID $data->kitID. It has committed orders.";
		}
		return true;
	}

	public static function getKit($data) {
		$fields = ['kitID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		if ($validate->kit($data->kitID) === false) {
			return false
		}
		$kit = InvKitQuery::create()->findOneByItemid($data->kitID);
		$response = array(
			'kitid'       => $data->kitID,
			'description' => $kit->item->description
		);
	}

	public static function validateKitComponent($data) {
		$fields = ['kitID|text', 'component|text'];
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
