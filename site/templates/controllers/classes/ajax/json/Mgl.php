<?php namespace Controllers\Ajax\Json;
// Dplus Model
use GlCodeQuery, GlCode;
// ProcessWire Classes, Modules
use ProcessWire\Module, ProcessWire\ProcessWire;
// Dplus Validators
use Dplus\CodeValidators\Mgl as MglValidator;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

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
