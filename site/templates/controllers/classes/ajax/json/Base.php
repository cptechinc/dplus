<?php namespace Controllers\Ajax\Json;
// Dplus Codes
use Dplus\Codes;
use Dplus\Codes\Base as CodeTable;
// Mvc Controllers
use Mvc\Controllers\Controller;

class Base extends Controller {
	public static function test() {
		return 'test';
	}

/* =============================================================
	Abstracted CodeTable functions
============================================================= */
	/**
	 * Validate Simple Code using CodeTable
	 * NOTE: only works for Simple Keys
	 * @param  CodeTable $manager  CodeTable
	 * @param  object    $data
	 *                        ->code  (string) Key
	 *                        ->jqv   (bool)   Send Response in JqueryValidate format
	 *                        ->new   (bool)   Validate if key can be used for a new code
	 * @return mixed
	 */
	protected static function validateCodeTableSimpleCode(CodeTable $manager, $data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$exists = $manager->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : $manager::DESCRIPTION . " $data->code already exists";
		}

		if ($exists === false) {
			return $manager::DESCRIPTION . " $data->code not found";
		}
		return true;
	}

	/**
	 * Return Code JSON, using CodeTable
	 * NOTE: only works for Simple Keys
	 * @param  CodeTable $manager     CodeTable
	 * @param  object    $data
	 *                        ->code  (string) Key
	 * @return false|array
	 */
	protected static function getCodeTableSimpleCode(CodeTable $manager, $data) {
		$fields = ['code|text'];
		self::sanitizeParametersShort($data, $fields);

		if ($manager->exists($data->code) === false) {
			return false;
		}
		return $manager->codeJson($manager->code($data->code));
	}
}
