<?php namespace Controllers\Ajax\Json;

use ProcessWire\Module, ProcessWire\ProcessWire;

use Mvc\Controllers\AbstractController;

use DplusUserQuery, DplusUser;

class Msa extends AbstractController {
	public static function test() {
		return 'test';
	}

	public static function validateUserid($data) {
		$fields = ['userID|text', 'loginID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();
		$userID = $data->loginID ? $data->loginID : $data->userID;

		if ($validate->loginid($userID) === false) {
			return "User $userID not found";
		}
		return true;
	}

	public static function getUserid($data) {
		$fields = ['userID|text', 'loginID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();
		$userID = $data->loginID ? $data->loginID : $data->userID;

		if ($validate->loginid($userID) === false) {
			false;
		}
		$login = DplusUserQuery::create()->findOneByLoginid($loginID);
		return array(
			'loginid' => $userID,
			'name'    => $login->name,
			'whseid'  => $login->whseid,
		);
	}

	private static function validator() {
		return self::pw('modules')->geT('ValidateMsa');
	}
}
