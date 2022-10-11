<?php namespace Controllers\Ajax\Json;
// Dplus CRUD
use Dplus\Codes;

class Mpr extends AbstractJsonController {
	public static function test() {
		return 'test';
	}

	public static function validateSourceExists($data) {
		$table = Codes\Mpr\Src::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getSource($data) {
		$table = Codes\Mpr\Src::getInstance();
		return self::getCodeTableCode($data, $table);
	}
}
