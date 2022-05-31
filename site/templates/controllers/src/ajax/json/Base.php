<?php namespace Controllers\Ajax\Json;
// Dplus Codes
use Dplus\Codes;
use Dplus\Codes\Base as CodeTable;
// Dplus Crud
use Dplus\Crud\Manager as CrudManager;
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

/* =============================================================
	Abstracted CRUDManager functions
============================================================= */
	/**
	 * Validate Simple Ids using CrudManager
	 * NOTE: only works for Simple Keys
	 * @param  CrudManager $manager  CrudManager
	 * @param  object      $data
	 *                          ->id  (string) Key
	 *                          ->jqv   (bool)   Send Response in JqueryValidate format
	 *                          ->new   (bool)   Validate if key can be used for a new code
	 * @return mixed
	 */
	protected static function validateCrudManagerSimpleId(CrudManager $manager, $data) {
		$fields = ['id|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$exists = $manager->exists($data->id);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : $manager::DESCRIPTION . " $data->id already exists";
		}

		if ($exists === false) {
			return $manager::DESCRIPTION . " $data->id not found";
		}
		return true;
	}

	/**
	 * Return Code JSON, using CrudManager
	 * NOTE: only works for Simple Keys
	 * @param  CrudManager $manager CrudManager
	 * @param  object      $data
	 *                          ->id  (string) Key
	 * @return false|array
	 */
	protected static function getCrudManagerSimpleRecord(CrudManager $manager, $data) {
		$fields = ['id|text'];
		self::sanitizeParametersShort($data, $fields);

		if ($manager->exists($data->id) === false) {
			return false;
		}
		return $manager->recordJson($manager->record($data->id));
	}
}
