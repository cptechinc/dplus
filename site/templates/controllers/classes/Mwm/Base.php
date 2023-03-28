<?php namespace Controllers\Wm;
// Purl Library
use Purl\Url as Purl;
// Dplus Model
use WarehouseQuery, Warehouse;
// Dpluso Model
use WhsesessionQuery, Whsesession;
// ProcessWire Classes, Modules
use ProcessWire\Module, ProcessWire\User;
use Processwire\SearchInventory, Processwire\WarehouseManagement;
// Dplus Classes
use Dplus\Session\UserMenuPermissions;
// Mvc Controllers
use Controllers\AbstractController;

class Base extends AbstractController {
	/** @var SearchInventory **/
	static private $inventory;

	/** @var WarehouseManagement **/
	static private $whsem;

	/** @var Warehouse **/
	static private $warehouse;

	/** @var Whsesession **/
	static private $whsesession;

	/** @var HtmlWriter **/
	static private $htmlwriter;

	/** @var string **/
	static public $sessionID;

	static public function redirect($url) {
		self::pw('session')->redirect($url, $http301 = false);
	}

	static public function getWarehouseManagement() {
		if (empty(self::$whsem)) {
			self::$whsem = self::pw('modules')->get('WarehouseManagement');
		}
		return self::$whsem;
	}

	static public function getInventorySearch() {
		if (empty(self::$inventory)) {
			self::$inventory = self::pw('modules')->get('SearchInventory');
		}
		return self::$inventory;
	}

	static public function getHtmlWriter() {
		if (empty(self::$htmlwriter)) {
			self::$htmlwriter = self::pw('modules')->get('HtmlWriter');
		}
		return self::$htmlwriter;
	}

	static public function getCurrentUserWarehouse() {
		if (empty(self::$warehouse)) {
			self::$warehouse = WarehouseQuery::create()->findOneById(self::pw('user')->whseid);
		}
		return self::$warehouse;
	}

	static public function setSessionid($sessionID = '') {
		self::$sessionID = $sessionID ? $sessionID : session_id();
	}

	static public function getSessionid() {
		if (empty(self::$sessionID)) {
			self::setSessionid();
		}
		return self::$sessionID;
	}

	static public function getWhseSession($sessionID = '') {
		if (empty(self::$whsesession)) {
			self::setSessionid($sessionID);
			self::$whsesession = WhsesessionQuery::create()->findOneBySessionid(self::$sessionID);
		}
		return self::$whsesession;
	}

/* =============================================================
	Displays
============================================================= */

/* =============================================================
	Validator, Module Getters
============================================================= */
	public static function validateUserPermission(User $user = null) {
		if (static::validateMenuPermissions($user) === false) {
			return false;
		}
		return parent::validateUserPermission($user);
	}

	public static function validateMenuPermissions(User $user = null) {
		$page   = self::pw('page');

		foreach ($page->parents('template=dplus-menu|warehouse-menu') as $parent) {
			$code = $parent->dplus_function ? $parent->dplus_function : $parent->dplus_permission;

			if (empty($code) === false && UserMenuPermissions::instance()->canAccess($code) === false) {
				return false;
			}
		}
	}
}
