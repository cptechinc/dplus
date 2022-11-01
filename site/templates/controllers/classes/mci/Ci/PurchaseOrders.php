<?php namespace Controllers\Mci\Ci;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Dplus Models
use Customer;
// ProcessWire
use ProcessWire\WireData;

/**
 * Ci\PurchaseOrders
 * 
 * Handles CI Purchase Orders Page
 */
class PurchaseOrders extends AbstractSubfunctionController {
	const PERMISSION_CIO = 'customerpo';
	const JSONCODE       = SalesHistory::JSONCODE;
	const TITLE          = 'CI: Purchase Orders';
	const SUMMARY        = 'View Purchase Orders';
	const SUBFUNCTIONKEY = 'purchase-orders';

/* =============================================================
	1. Indexes
============================================================= */
	public static function index(WireData $data) {
		$fields = ['rid|int', 'custpo|text', 'refresh|bool'];
		self::sanitizeParametersShort($data, $fields);
		self::throw404IfInvalidCustomerOrPermission($data);
		$data->custID = self::getCustidByRid($data->rid);

		if ($data->refresh) {
			self::requestJson(self::prepareJsonRequest($data));
			self::pw('session')->redirect(self::ordersUrl($data->rid, $data->custpo), $http301 = false);
		}

		if (empty($data->custpo)) {
			$jsonm  = self::getJsonFileFetcher();
			$jsonm->delete(SalesHistory::JSONCODE);
			$jsonm->delete(SalesOrders::JSONCODE);
			return self::displayInit($data);
		}
		return self::custpo($data);
	}

	private static function custpo(WireData $data) {
		self::fetchData($data);
		self::pw('page')->headline = "CI: $data->custID Orders that match '$data->custpo'";
		$customer  = self::getCustomerByRid($data->rid);
		self::setSessionVar($data->custpo, 'custpo');

		self::initHooks();
		$html = '';
		$html .= self::displayCustpo($data, $customer);
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
		return self::ordersUrl($data->rid, $data->custpo, $refresh=true);
	}

	protected static function prepareJsonRequest(WireData $data) {
		$fields = ['rid|int', 'custID|string', 'custpo|text', 'sessionID|text'];
		self::sanitizeParametersShort($data, $fields);
		if (empty($data->custID)) {
			$data->custID = self::getCustidByRid($data->rid);
		}
		return ['CICUSTPO', "CUSTID=$data->custID", "SHIPID=$data->shiptoID", "CUSTPO=$data->custpo"];
	}

/* =============================================================
	4. URLs
============================================================= */
	public static function ordersUrl($ridID, $custpo = '', $refreshdata = false) {
		$url = new Purl(self::ciPurchaseOrdersUrl($ridID));

		if ($custpo) {
			$url->query->set('custpo', $custpo);

			if ($refreshdata) {
				$url->query->set('refresh', 'true');
			}
		}
		return $url->getUrl();
	}

/* =============================================================
	5. Displays
============================================================= */
	private static function displayInit(WireData $data) {
		return self::renderInit($data);
	}

	protected static function displayCustpo(WireData $data, Customer $customer) {
		$html = new WireData();
		$html->history = self::renderSalesHistory($data);
		$html->orders  = self::renderSalesOrders($data);
		self::addPageData($data);
		return self::pw('config')->twig->render('customers/ci/.new/purchase-orders/display.twig', ['customer' => $customer, 'html' => $html]);
	}

/* =============================================================
	6. HTML Rendering
============================================================= */
	private static function renderInit(WireData $data) {
		return self::pw('config')->twig->render('customers/ci/.new/purchase-orders/init/display.twig');
	}

	private static function renderSalesHistory(WireData $data) {
		$jsonm  = self::getJsonFileFetcher();
		$json   = $jsonm->getFile(SalesHistory::JSONCODE);

		if ($jsonm->exists(SalesHistory::JSONCODE) === false) {
			return self::renderJsonNotFoundAlert($data, 'Sales History');
		}

		if ($json['error']) {
			return self::renderJsonError($data, $json);
		}

		$formatter = SalesHistory::getFormatter();
		$docm      = SalesHistory::getDocFinder();
		return self::pw('config')->twig->render('customers/ci/.new/sales-history/sales-history.twig', ['json' => $json, 'formatter' => $formatter, 'blueprint' => $formatter->get_tableblueprint(), 'docm' => $docm]);
	}

	private static function renderSalesOrders(WireData $data) {
		$jsonm  = self::getJsonFileFetcher();
		$json   = $jsonm->getFile(SalesOrders::JSONCODE);

		if ($jsonm->exists(SalesOrders::JSONCODE) === false) {
			return self::renderJsonNotFoundAlert($data, 'Sales Orders');
		}

		if ($json['error']) {
			return self::renderJsonError($data, $json);
		}

		$formatter = SalesOrders::getFormatter();
		$docm      = SalesOrders::getDocFinder();
		return self::pw('config')->twig->render('customers/ci/.new/sales-orders/sales-orders.twig', ['json' => $json, 'formatter' => $formatter, 'blueprint' => $formatter->get_tableblueprint(), 'docm' => $docm]);
	}

/* =============================================================
	7. Class / Module Getting
============================================================= */

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
