<?php namespace Controllers\Ajax\Json;
// Propel
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
// ProcessWire Classes, Modules
use ProcessWire\Module, ProcessWire\ProcessWire;
// Dplus Validators
use Dplus\CodeValidators\Mqo     as MqoValidator;
// Mvc Controllers
use Mvc\Controllers\Controller;

class Mqo extends Controller {
	public static function test() {
		return 'test';
	}

	public static function editQuote($data) {
		self::sanitizeParametersShort($data, ['qnbr|text']);
		$validate = self::validator();

		if ($validate->quote($data->qnbr) === false) {
			return false;
		}
		$eqo = self::pw('modules')->get('Eqo');
		$eqo->setQnbr($data->qnbr);
		$eqo->processInput(self::pw('input'));
		return true;
	}

	private static function validator() {
		return new MqoValidator();
	}
}
