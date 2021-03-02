<?php namespace Controllers\Mwm;

use Mvc\Controllers\AbstractController;
use Controllers\Dplus\Menu as Dmenu;

use Dplus\CodeValidators\Min as MinValidator;
use WhsesessionQuery, Whsesession;

class Menu extends AbstractController {
	public static function index($data) {
		if (self::sessionExists() === false) {
			self::requestWhseSessionLogin();
			self::pw('session')->redirect(self::pw('page')->url, $http301 = false);
		}
		Dmenu::index($data);
	}

	public static function requestWhseSessionLogin() {
		$loginM = self::pw('modules')->get('DplusUser');
		$loginM->request_login_whse(self::pw('user')->loginid);
	}

	public static function sessionExists($sessionID = '') {
		$sessionID = $sessionID ? $sessionID : session_id();
		$q = WhsesessionQuery::create()->filterBySessionid($sessionID);
		return boolval($q->count());
	}

	public static function sessionWhseExists($sessionID = '') {
		$sessionID = $sessionID ? $sessionID : session_id();
		$q = WhsesessionQuery::create()->filterBySessionid($sessionID);
		$whseID = $q->select('whseid')->findOne();
		$validate = new MinValidator();
		return $validate->whseid($whseID);
	}

	public function getWhseSession($sessionID = '') {
		$q = WhsesessionQuery::create()->filterBySessionid($sessionID);
		return $q->findOne();
	}
}
