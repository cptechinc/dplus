<?php namespace Controllers\Mii;

// Dplus Model
use ItemMasterItemQuery, ItemMasterItem;
use ItemPricingQuery, ItemPricing;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\CiLoadCustomerShipto;
// Dplus Validators
use Dplus\CodeValidators\Min as MinValidator;
// Dplus Filters
use Dplus\Filters\Min\ItemMaster  as ItemMasterFilter;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

abstract class IiFunction extends AbstractController {
	const PERMISSION     = 'ii';
	const PERMISSION_IIO = '';

	private static $validator;
	private static $iio;
	private static $jsonm;
	private static $filehasher;


/* =============================================================
	Validations
============================================================= */
	protected static function validateItemidPermission($data) {
		self::sanitizeParametersShort($data, ['itemID|text']);

		if (self::validateItemid($data->itemID) === false) {
			return false;
		}

		if (self::validateUserPermission($data) === false) {
			return false;
		}
		return true;
	}

	protected static function validateItemid($itemID) {
		$validate = self::getValidator();
		return $validate->itemid($itemID);
	}

	protected static function validateUserPermission($data) {
		$user = self::pw('user');
		$iio  = self::getIio();

		if ($user->has_function(self::PERMISSION) === false) {
			return false;
		}

		if ($iio->allowUser($user, static::PERMISSION_IIO) === false) {
			return false;
		}
		return true;
	}

/* =============================================================
	Data Requests
============================================================= */
	public static function requestIiItem($itemID, $sessionID = '') {
		$sessionID = $sessionID ? $sessionID : session_id();
		$db = self::pw('modules')->get('DplusOnlineDatabase')->db_name;
		$data = array('IISELECT', "ITEMID=$itemID");
		self::sendRequest($data, $sessionID);
	}

	protected static function sendRequest(array $data, $sessionID = '') {
		$sessionID = $sessionID ? $sessionID : session_id();
		$db = self::pw('modules')->get('DplusOnlineDatabase')->db_name;
		$data = array_merge(["DBNAME=$db"], $data);
		$requestor = self::pw('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $sessionID);
		$requestor->cgi_request(self::pw('config')->cgis['default'], $sessionID);
	}

/* =============================================================
	Classes, Module Getters
============================================================= */
	protected static function getFileHasher() {
		if (empty(self::$filehasher)) {
			self::$filehasher = self::pw('modules')->get('FileHasher');
		}
		return self::$filehasher;
	}

	public static function getValidator() {
		if (empty(self::$validator)) {
			self::$validator = new MinValidator();
		}
		return self::$validator;
	}

	public static function getIio() {
		if (empty(self::$iio)) {
			self::$iio = self::pw('modules')->get('Iio');
		}
		return self::$iio;
	}

	public static function getJsonModule() {
		if (empty(self::$jsonm)) {
			self::$jsonm = self::pw('modules')->get('JsonDataFilesSession');
		}
		return self::$jsonm;
	}

	public static function getItmItem($itemID) {
		return self::pw('modules')->get('Itm')->item($itemID);
	}

/* =============================================================
	Displays
============================================================= */
	protected static function alertInvalidItemPermissions($data) {
		self::sanitizeParametersShort($data, ['itemID|text']);
		if (empty($data->itemID) === false && self::validateItemid($data->itemID) === false) {
			return self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Item $data->itemID could not be found"]);
		}
		if (self::validateUserPermission($data) === false) {
			return self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "You don't have Permission for this Function"]);
		}
		return '';
	}

	protected static function breadCrumbs() {
		return self::pw('config')->twig->render('items/ii/bread-crumbs.twig');
	}
}
