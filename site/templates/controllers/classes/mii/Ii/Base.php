<?php namespace Controllers\Mii\Ii;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// ProcessWire
use ProcessWire\User;
// Dplus Validators
use Dplus\CodeValidators\Min as MinValidator;
// Dplus Databases
use Dplus\Databases\Connectors\Dpluso as DbDpluso;
// Dplus
use Dplus\Session\UserMenuPermissions;
// Controllers
use Controllers\AbstractController;

abstract class Base extends AbstractController {
	const PARENT_MENU_CODE = 'mii';
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

		if (self::validateUserPermission() === false) {
			return false;
		}
		return true;
	}

	protected static function validateItemid($itemID) {
		$validate = self::getValidator();
		return $validate->itemid($itemID);
	}

	public static function validateUserPermission(User $user = null) {
		$user = $user ? $user : self::pw('user');

		$MCP = UserMenuPermissions::instance();

		if ($MCP->canAccess(static::PARENT_MENU_CODE) === false) {
			return false;
		}

		if (parent::validateUserPermission($user) === false) {
			return false;
		}
		$iio = self::getIio();
		
		if ($iio->allowUser($user, static::PERMISSION_IIO) === false) {
			return false;
		}
		return true;
	}

	public static function jsonItemidMatches($jsonItemID, $itemID) {
		return stripslashes($jsonItemID) == $itemID;
	}

/* =============================================================
	Data Requests
============================================================= */
	public static function requestIiItem($itemID, $sessionID = '') {
		$sessionID = $sessionID ? $sessionID : session_id();
		$db = DbDpluso::instance()->dbconfig->dbName;
		$data = array('IISELECT', "ITEMID=$itemID");
		self::sendRequest($data, $sessionID);
	}

	protected static function sendRequest(array $data, $sessionID = '') {
		$sessionID = $sessionID ? $sessionID : session_id();
		$db = DbDpluso::instance()->dbconfig->dbName;
		$data = array_merge(["DBNAME=$db"], $data);
		$requestor = self::pw('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $sessionID);
		$requestor->cgi_request(self::pw('config')->cgis['default'], $sessionID);
	}

/* =============================================================
	URLs
============================================================= */
	public static function iiUrl($itemID = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=ii-item')->url);
		if ($itemID) {
			$url->query->set('itemID', $itemID);
		}
		return $url->getUrl();
	}

/* =============================================================
	Classes, Module Getters
============================================================= */
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
		if (self::validateUserPermission() === false) {
			return self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "You don't have Permission for this Function"]);
		}
		self::sanitizeParametersShort($data, ['itemID|text']);
		if (empty($data->itemID) === false && self::validateItemid($data->itemID) === false) {
			return self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Item $data->itemID could not be found"]);
		}
		return '';
	}

	protected static function breadCrumbs() {
		return self::pw('config')->twig->render('items/ii/bread-crumbs.twig');
	}
}
