<?php namespace Controllers\Ajax\Json;
// Dplus Model
use DplusUserQuery, DplusUser;
// ProcessWire Classes, Modules
use ProcessWire\Module, ProcessWire\ProcessWire;
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

		if ($validate->userid($userID) === false) {
			if ($data->new) {
				return "User $userID not found";
			}
			return "User $userID Already Exists";
		}
		return true;
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

	private static function validator() {
		return new MsaValidator();
	}
}
