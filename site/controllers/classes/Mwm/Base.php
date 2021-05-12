<?php namespace Controllers\Wm;

use stdClass;
// Purl Library
use Purl\Url as Purl;
// Dplus Model
use WarehouseQuery, Warehouse;
// Dpluso Model
use BininfoQuery, Bininfo;
use WhsesessionQuery, Whsesession;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module, Processwire\SearchInventory, Processwire\WarehouseManagement;
// Dplus Classes
use Dplus\Wm\Binr as BinrCRUD;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

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
}
