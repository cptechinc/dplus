<?php namespace Controllers\Mci\Ci;
// ProcessWire
use ProcessWire\Wire404Exception;
use ProcessWire\WireData;
// Dplus Databases
use Dplus\Databases\Connectors\Dpluso as DbDpluso;
// Dplus Mar
use Dplus\Mar\Armain\Cmm;

abstract class AbstractSubfunctionController extends AbstractController {
	const DPLUSPERMISSION = 'ci';
	const PERMISSION_CIO  = '';
	const TITLE      = 'Customer Information';
	const SUMMARY    = 'View Customer Information';
	const JSONCODE = '';
	const SUBFUNCTIONKEY = '';

	private static $jsonm;

	public static function index(WireData $data) {
		$fields = ['rid|int'];
		self::sanitizeParametersShort($data, $fields);
		self::throw404IfInvalidCustomerOrPermission($data);
	} 

/* =============================================================
	Validation
============================================================= */
	protected static function throw404IfInvalidCustomerOrPermission(WireData $data) {
		if (self::validateUserPermission() === false) {
			throw new Wire404Exception();
		}
		$cmm = Cmm::instance();
		if (self::validateUserHasCustomerPermission(null, $cmm->custidByRid($data->rid)) === false) {
			throw new Wire404Exception();
		}
	}

/* =============================================================
	Data Requests
============================================================= */
	abstract protected static function prepareJsonRequest(WireData $data);
	
	protected static function requestJson($data = [], $sessionID = '') {
		if (empty($data)) {
			return false;
		}
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
	Data Retrieval
============================================================= */
	/**
	 * Retreive JSON
	 * @param  WireData $data
	 * @return array
	 */
	protected static function fetchData(WireData $data) {
		$data    = self::sanitizeParametersShort($data, ['rid|int', 'itemID|text']);
		$jsonFetcher   = self::getJsonFileFetcher();
		$json    = $jsonFetcher->getFile(static::JSONCODE);
		$session = self::pw('session');

		if ($jsonFetcher->exists(static::JSONCODE)) {
			if (static::validateJsonFileMatches($data, $json) === false) {
				$jsonFetcher->delete(static::JSONCODE);
				$session->redirect(static::fetchDataRedirectUrl($data), $http301=false);
			}
			$session->removeFor('ci', static::SUBFUNCTIONKEY);
			return $json;
		}

		if ($session->getFor('ci', static::SUBFUNCTIONKEY) > 3) {
			return [];
		}
		$session->setFor('ci', static::SUBFUNCTIONKEY, ($session->getFor('ci', static::SUBFUNCTIONKEY) + 1));
		$session->redirect(static::fetchDataRedirectUrl($data), $http301=false);
	}

	/**
	 * Return URL to Fetch Data
	 * @param  WireData $data
	 * @return string
	 */
	abstract protected static function fetchDataRedirectUrl(WireData $data);

	/**
	 * Return if JSON Data matches for this Customer ID
	 * @param  WireData $data
	 * @param  array    $json
	 * @return bool
	 */
	protected static function validateJsonFileMatches(WireData $data, array $json) {
		return $json['custid'] == self::getCustidByRid($data->rid);
	}

/* =============================================================
	Classes, Module Getters
============================================================= */
	public static function getJsonFileFetcher() {
		if (empty(self::$jsonm)) {
			self::$jsonm = self::pw('modules')->get('JsonDataFilesSession');
		}
		return self::$jsonm;
	}

/* =============================================================
	Render HTML
============================================================= */
	protected static function renderJsonNotFoundAlert(WireData $data, $filedesc) {
		return self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' =>  $filedesc.' File Not Found']);
	}

	protected static function renderJsonError(WireData $data, array $json) {
		return self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
	}

}
