<?php namespace Controllers\Ajax\Json;
// ProcessWire
use ProcessWire\WireData;
// Dplus
use Dplus\Codes;
use Dplus\Codes\AbstractCodeTable;
use Dplus\UserOptions;
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

	protected static function validateUserOptionsUserid(WireData $data, UserOptions\AbstractManager $table, $codedesc = '') {
		$fields = ['userID|string', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$codedesc = $codedesc ? $codedesc : $table::DESCRIPTION_RECORD;

		$exists = $table->exists($data->userID);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "$codedesc '$data->userID' already exists";
		}

		if ($exists === false) {
			return "$codedesc '$data->userID' not found";
		}
		return true;
	}

	protected static function getUserOptionsUser(WireData $data, UserOptions\AbstractManager $table) {
		self::sanitizeParametersShort($data, ['userID|string']);

		if ($table->exists($data->userID) === false) {
			return false;
		}
		return $table->userJson($table->user($data->userID));
	}

}