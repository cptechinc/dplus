<?php namespace Controllers\Ajax\Json;
// Dplus Model
use DplusUserQuery, DplusUser;
// ProcessWire Classes, Modules
use ProcessWire\Module, ProcessWire\ProcessWire;
// Dplus Codes
use Dplus\Codes\Msa as MsaCodes;
// Dplus Validators
use Dplus\CodeValidators\Msa as MsaValidator;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Msa extends AbstractController {
	public static function test() {
		return 'test';
	}

	public static function validateUserid($data) {
		$fields = ['userID|text', 'loginID|text', 'jqv|bool', 'new|bool'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();
		$userID = $data->loginID ? $data->loginID : $data->userID;
		$exists = $validate->userid($userID);

		if ($data->jqv === false) {
			if ($data->new) {
				return $exists === false;
			}
			return $exists;
		}

		if ($data->new) {
			return $exists === false ? true : "User $userID Exists";
		}
		return $exists ? true : "User $userID Not Found";
	}

	public static function getUserid($data) {
		$fields = ['userID|text', 'loginID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();
		$userID = $data->loginID ? $data->loginID : $data->userID;

		if ($validate->userid($userID) === false) {
			return false;
		}
		$login = DplusUserQuery::create()->findOneByLoginid($loginID);
		return array(
			'loginid' => $userID,
			'name'    => $login->name,
			'whseid'  => $login->whseid,
		);
	}

	public static function validateLgrp($data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$lgrp = MsaCodes\Lgrp::getInstance();
		$exists = $lgrp->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "Login Group $data->code already exists";
		}

		if ($exists === false) {
			return "Login Group $data->code not found";
		}
		return true;
	}

	public static function getLgrp($data) {
		self::sanitizeParametersShort($data, ['code|text']);

		$lgrp = MsaCodes\Lgrp::getInstance();
		if ($lgrp->exists($data->code) === false) {
			return false;
		}
		$code = $lgrp->code($data->code);
		$response = [
			'code'         => $code->code,
			'description'  => $code->description,
		];
		return $response;
	}

	private static function validator() {
		return new MsaValidator();
	}
}
