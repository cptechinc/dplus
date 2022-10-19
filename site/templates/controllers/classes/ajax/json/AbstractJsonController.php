<?php namespace Controllers\Ajax\Json;
// ProcessWire
use ProcessWire\WireData;
// Dplus Codes
use Dplus\Codes;
use Dplus\Codes\AbstractCodeTable;
// Mvc Controllers
use Mvc\Controllers\Controller;

class AbstractJsonController extends Controller{
	public static function test() {
		return 'test';
	}

	protected static function validateCodeTableCode(WireData $data, AbstractCodeTable $table, $codedesc = '') {
		$fields = ['code|string', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$codedesc = $codedesc ? $codedesc : $table::DESCRIPTION_RECORD;

		$exists = $table->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "$codedesc '$data->code' already exists";
		}

		if ($exists === false) {
			return "$codedesc '$data->code' not found";
		}
		return true;
	}

	protected static function getCodeTableCode(WireData $data, AbstractCodeTable $table) {
		self::sanitizeParametersShort($data, ['code|string']);

		if ($table->exists($data->code) === false) {
			return false;
		}
		return $table->codeJson($table->code($data->code));
	}

}