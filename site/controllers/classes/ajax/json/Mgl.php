<?php namespace Controllers\Ajax\Json;

use ProcessWire\Module, ProcessWire\ProcessWire;

use Mvc\Controllers\AbstractController;

use Dplus\CodeValidators\Mgl as MglValidator;
use GlCodeQuery, GlCode;

class Mgl extends AbstractController {
	public static function test() {
		return 'test';
	}

	public static function validateGlCode($data) {
		$fields = ['code|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		if ($validate->glCode($data->code) === false) {
			if (empty($data->jqv) === false) {
				return "General Ledger $data->code not found";
			}
			return false;
		}
		return true;
	}

	public static function getGlCode($data) {
		$fields = ['code|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		if ($validate->glCode($data->code) === false) {
			return false;
		}
		$account = GlCodeQuery::create()->findOneByCode($data->code);
		return array(
			'code'        => $data->code,
			'description' => $account->description,
		);
	}

	private static function validator() {
		return new MglValidator();
	}
}
