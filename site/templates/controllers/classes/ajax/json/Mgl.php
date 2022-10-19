<?php namespace Controllers\Ajax\Json;
// Dplus Code Tables
use Dplus\Codes;
use Dplus\Codes\Mgl\Ttm;
use Dplus\Codes\Mgl\Dtm;
// Dplus Validators
use Dplus\CodeValidators\Mgl as MglValidator;

class Mgl extends AbstractJsonController {
	public static function test() {
		return 'test';
	}

	public static function validateGlCode($data) {
		$table = Codes\Mgl\Mhm::instance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getGlCode($data) {
		$table = Codes\Mgl\Mhm::instance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateTtmCode($data) {
		$table = Ttm::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getTtmCode($data) {
		$table = Ttm::getInstance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateDtmCode($data) {
		$table = Dtm::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getDtmCode($data) {
		$table = Dtm::getInstance();
		return self::getCodeTableCode($data, $table);
	}
}
