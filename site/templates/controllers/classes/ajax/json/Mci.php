<?php namespace Controllers\Ajax\Json;
// PrcessWire
use ProcessWire\WireData;
// Dplus
use Dplus\CodeValidators\Mar as MarValidator;
use Dplus\UserOptions;

class Mci extends AbstractJsonController {

	public static function validateCustid($data) {
		$fields = ['custID|string', 'jqv|bool'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		if ($validate->custid($data->custID) === false) {
			return $data->jqv ? "Customer $data->custID not found" : false;
		}
		return true;
	}

	public static function validateCioUserid(WireData $data) {
		return self::validateUserOptionsUserid($data, UserOptions\Cio::getInstance());
	}

	public static function getCioUser(WireData $data) {
		return self::getUserOptionsUser($data, UserOptions\Cio::getInstance());
	}

	private static function validator() {
		return new MarValidator();
	}
}



