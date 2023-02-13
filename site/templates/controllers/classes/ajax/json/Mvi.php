<?php namespace Controllers\Ajax\Json;
// ProcessWire
use ProcessWire\WireData;
// Dplus 
use Dplus\UserOptions;


class Mvi extends AbstractJsonController {

	
	public static function validateVioUserid(WireData $data) {
		return self::validateUserOptionsUserid($data, UserOptions\Vio::getInstance());
	}

	public static function getVioUser(WireData $data) {
		return self::getUserOptionsUser($data, UserOptions\Vio::getInstance());
	}
}
