<?php namespace Controllers\Mci\Ci;
// ProcessWire
use ProcessWire\JsonDataFilesSession;
use ProcessWire\Page;
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

	/** @var JsonDataFilesSession */
	private static $jsonm;

/* =============================================================
	1. Indexes
============================================================= */
	public static function index(WireData $data) {
		$fields = ['rid|int'];
		self::sanitizeParametersShort($data, $fields);
		self::throw404IfInvalidCustomerOrPermission($data);
	} 

/* =============================================================
	2. Validations
============================================================= */
	/**
	 * Throw 404 Error if 
	 * 1. Customer is Invalid
	 * 2. User is Not Permitted
	 * @throws Wire404Exception
	 * @param  WireData $data
	 * @return void
	 */
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
	3.b.  Data Requests
============================================================= */
	/**
	 * Return Request Array
	 * @param  WireData $data
	 * @return array           [CISALESORDER, 'CUSTID=$CUSTID']
	 */
	protected static function prepareJsonRequest(WireData $data) {
		return [];
	}
	
	/**
	 * Send Request Array
	 * @param  array  $data
	 * @param  string $sessionID
	 * @return bool
	 */
	protected static function requestJson($data = [], $sessionID = '') {
		if (empty($data)) {
			return false;
		}
		return self::sendRequest($data, $sessionID);
	}

	/**
	 * Write Request File, Send Request
	 * @param  array  $data
	 * @param  string $sessionID
	 * @return bool
	 */
	protected static function sendRequest(array $data, $sessionID = '') {
		$sessionID = $sessionID ? $sessionID : session_id();
		$db = DbDpluso::instance()->dbconfig->dbName;
		$data = array_merge(["DBNAME=$db"], $data);
		$requestor = self::pw('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $sessionID);
		return $requestor->cgi_request(self::pw('config')->cgis['default'], $sessionID);
	}

/* =============================================================
	3.c. Data Retrieval
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
			static::deleteSessionVar();
			return $json;
		}

		if (static::getSessionVar() > 3) {
			return [];
		}
		static::setSessionVar((static::getSessionVar() + 1));
		$session->redirect(static::fetchDataRedirectUrl($data), $http301=false);
	}

	/**
	 * Return URL to Fetch Data
	 * @param  WireData $data
	 * @return string
	 */
	protected static function fetchDataRedirectUrl(WireData $data) {
		return '';
	}

	/**
	 * Return if JSON Data matches for this Customer ID
	 * @param  WireData $data
	 * @param  array    $json
	 * @return bool
	 */
	protected static function validateJsonFileMatches(WireData $data, array $json) {
		self::decorateInputDataWithCustid($data);
		return $json['custid'] == $data->custID;
	}

/* =============================================================
	4. URLs
============================================================= */

/* =============================================================
	5. Displays
============================================================= */

/* =============================================================
	6. HTML Rendering
============================================================= */
	/**
	 * Return JSON not found alert
	 * @param  WireData $data
	 * @param  string   $filedesc
	 * @return string
	 */
	protected static function renderJsonNotFoundAlert(WireData $data, $filedesc) {
		self::addPageData($data);
		$message = $filedesc.' File Not Found';
		
		return self::pw('config')->twig->render('customers/ci/.new/errors/json-not-found.twig', ['message' => $message]);
	}

	/**
	 * Return JSON Error Alert
	 * @param  WireData $data
	 * @param  array    $json
	 * @return string
	 */
	protected static function renderJsonError(WireData $data, array $json) {
		return self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
	}

/* =============================================================
	7. Class / Module Getters
============================================================= */
	/**
	 * Return JSON File Fetcher
	 * @return JsonDataFilesSession
	 */
	public static function getJsonFileFetcher() {
		if (empty(self::$jsonm)) {
			self::$jsonm = self::pw('modules')->get('JsonDataFilesSession');
		}
		return self::$jsonm;
	}

/* =============================================================
	8. Supplemental
============================================================= */

/* =============================================================
	9. Hooks / Object Decorating
============================================================= */
	/**
	 * Decorate Page with extra Properties
	 * @param  WireData  $data
	 * @param  Page|null $page
	 * @return void
	 */
	protected static function addPageData(WireData $data, Page $page = null) {
		$page = $page ? $page : self::pw('page');
		$page->refreshurl   = static::fetchDataRedirectUrl($data);
		$page->lastmodified = self::getJsonFileFetcher()->lastModified(static::JSONCODE);
	}

/* =============================================================
	10. Sessions
============================================================= */
	/**
	 * Set Session Variable
	 * @param  string $value
	 * @param  string $key
	 * @return bool
	 */
	public static function setSessionVar($value, $key = '') {
		$key = $key ? $key : static::SUBFUNCTIONKEY;
		return self::pw('session')->setFor('ci', $key, $value);
	}

	/**
	 * Return Session Variable
	 * @param  string $value
	 * @param  string $key
	 * @return mixed
	 */
	public static function getSessionVar($key = '') {
		$key = $key ? $key : static::SUBFUNCTIONKEY;
		return self::pw('session')->getFor('ci', $key);
	}

	/**
	 * Delete Session Variable
	 * @param  string $key
	 * @return bool
	 */
	public static function deleteSessionVar($key = '') {
		$key = $key ? $key : static::SUBFUNCTIONKEY;
		return self::pw('session')->removeFor('ci', $key);
	}
}
