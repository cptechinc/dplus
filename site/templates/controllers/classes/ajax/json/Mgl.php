<?php namespace Controllers\Ajax\Json;
// Dplus Model
use GlCodeQuery, GlCode;
// ProcessWire Classes, Modules
use ProcessWire\Module, ProcessWire\ProcessWire;
// Dplus Code Tables
use Dplus\Codes\Mgl\Ttm;
use Dplus\Codes\Mgl\Dtm;
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

	public static function validateStmtCode($data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$ttm = Ttm::getInstance();
		$exists = $ttm->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "Statement $data->code already exists";
		}

		if ($exists === false) {
			return "Statement $data->code not found";
		}
		return true;
	}

	public static function getStmtCode($data) {
		self::sanitizeParametersShort($data, ['code|text']);

		$ttm = Ttm::getInstance();
		if ($ttm->exists($data->code) === false) {
			return false;
		}
		$code = $ttm->code($data->code);
		return $ttm->codeJson($code);
	}

	public static function validateDistCode($data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$dtm = Dtm::getInstance();
		$exists = $dtm->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "Distribution $data->code already exists";
		}

		if ($exists === false) {
			return "Distribution $data->code not found";
		}
		return true;
	}

	public static function getDistCode($data) {
		self::sanitizeParametersShort($data, ['code|text']);

		$dtm = Dtm::getInstance();
		if ($dtm->exists($data->code) === false) {
			return false;
		}
		$code = $dtm->code($data->code);
		return $dtm->codeJson($code);
	}

	private static function validator() {
		return new MglValidator();
	}
}
