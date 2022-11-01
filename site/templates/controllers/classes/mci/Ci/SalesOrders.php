<?php namespace Controllers\Mci\Ci;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Dplus Models
use Customer;
// ProcessWire
use ProcessWire\WireData;
// Dplus Screen Formatters
use Dplus\ScreenFormatters\Ci\SalesOrders as Formatter;
// Alias Document Finders
use Dplus\DocManagement\Finders as DocFinders;

/**
 * Ci\SalesOrders
 * 
 * Handles the CI Sales Orders Page
 */
class SalesOrders extends AbstractSubfunctionController {
	const PERMISSION_CIO = 'salesorders';
	const JSONCODE       = 'ci-sales-orders';
	const TITLE          = 'CI: Sales Orders';
	const SUMMARY        = 'View Sales Orders';
	const SUBFUNCTIONKEY = 'sales-orders';

/* =============================================================
	1. Indexes
============================================================= */
	public static function index(WireData $data) {
		$fields = ['rid|int', 'refresh|bool'];
		self::sanitizeParametersShort($data, $fields);
		self::throw404IfInvalidCustomerOrPermission($data);
		$data->custID = self::getCustidByRid($data->rid);

		if ($data->refresh) {
			self::requestJson(self::prepareJsonRequest($data));
			self::pw('session')->redirect(self::ciSalesOrdersUrl($data->rid), $http301 = false);
		}
		return self::orders($data);
	}

	private static function orders(WireData $data) {
		$json = self::fetchData($data);
		$customer = self::getCustomerByRid($data->rid);

		self::initHooks();
		self::pw('page')->headline = "CI: $customer->name Sales Orders";

		$html = '';
		$html .= self::displayOrders($data, $customer, $json);
		return $html;
	}

/* =============================================================
	2. Validations
============================================================= */

/* =============================================================
	3. Data Fetching / Requests / Retrieval
============================================================= */
	/**
	 * Return URL to Fetch Data
	 * @param  WireData $data
	 * @return string
	 */
	protected static function fetchDataRedirectUrl(WireData $data) {
		return self::ordersUrl($data->rid, $refresh=true);
	}

	/**
	 * Return if JSON Data matches for this Customer ID
	 * @param  WireData $data
	 * @param  array    $json
	 * @return bool
	 */
	protected static function validateJsonFileMatches(WireData $data, array $json) {
		return $json['custid'] == self::getCustidByRid($data->rid);
	}

	protected static function fetchData(WireData $data) {
		$jsonFetcher = self::getJsonFileFetcher();
		if ($jsonFetcher->exists(self::JSONCODE) && empty(PurchaseOrders::getSessionPo()) === false) {
			$jsonFetcher->delete(self::JSONCODE);
			PurchaseOrders::deleteSessionPo();
		}
		return parent::fetchData($data);
	}

	protected static function prepareJsonRequest(WireData $data) {
		$fields = ['rid|int', 'itemID|text', 'custID|string', 'sessionID|text'];
		self::sanitizeParametersShort($data, $fields);
		if (empty($data->custID)) {
			$data->custID = self::getCustidByRid($data->rid);
		}
		return ['CISALESORDR', "CUSTID=$data->custID", "SHIPID=$data->shiptoID", "SALESORDRNBR=", "ITEMID="];
	}

/* =============================================================
	4. URLs
============================================================= */
	public static function ordersUrl($rID, $refreshdata = false) {
		$url = new Purl(self::ciSalesOrdersUrl($rID));

		if ($refreshdata) {
			$url->query->set('refresh', 'true');
		}
		return $url->getUrl();
	}

/* =============================================================
	5. Displays
============================================================= */
	protected static function displayOrders(WireData $data, Customer $customer, $json = []) {
		if (empty($json)) {
			return self::renderJsonNotFoundAlert($data, 'Sales Orders');
		}

		if ($json['error']) {
			return self::renderJsonError($data, $json);
		}
		self::addPageData($data);
		return self::renderOrders($data, $customer, $json);
	}

/* =============================================================
	6. HTML Rendering
============================================================= */
	protected static function renderOrders(WireData $data, Customer $customer, array $json) {
		$formatter = self::getFormatter();
		$docm      = self::getDocFinder();
		return self::pw('config')->twig->render('customers/ci/.new/sales-orders/display.twig', ['customer' => $customer, 'json' => $json, 'formatter' => $formatter, 'blueprint' => $formatter->get_tableblueprint(), 'docm' => $docm]);
	}

/* =============================================================
	7. Class / Module Getting
============================================================= */
	// NOTE: Keep public, it's used in Ci\PurchaseOrders
	public static function getFormatter() {
		$f = new Formatter();
		$f->init_formatter();
		return $f;
	}

	// NOTE: Keep public, it's used in Ci\PurchaseOrders
	public static function getDocFinder() {
		return new DocFinders\SalesOrder();
	}

/* =============================================================
	8. Supplemental
============================================================= */

/* =============================================================
	9. Hooks / Object Decorating
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMci');

		$m->addHook('Page(pw_template=ci)::documentListUrl', function($event) {
			$event->return = Documents::documentsUrlSalesorder($event->arguments(0), $event->arguments(1));
		});
	}
}
