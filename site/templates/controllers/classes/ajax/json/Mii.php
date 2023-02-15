<?php namespace Controllers\Ajax\Json;
// ProcessWire
use ProcessWire\WireData;
// Dplus 
use Dplus\UserOptions;


class Mii extends AbstractJsonController {
	public static function validateIioUserid(WireData $data) {
		return self::validateUserOptionsUserid($data, UserOptions\Iio::getInstance());
	}

	public static function getIioUser(WireData $data) {
		return self::getUserOptionsUser($data, UserOptions\Iio::getInstance());
	}
}
