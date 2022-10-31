<?php namespace Controllers\Mci\Ci;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Dplus Models
use Customer;
// ProcessWire
use ProcessWire\WireData;
// Dplus Screen Formatters
use Dplus\ScreenFormatters\Ci\SalesHistory as Formatter;
// Alias Document Finders
use Dplus\DocManagement\Finders as DocFinders;

class SalesHistory extends AbstractSubfunctionController {
	const PERMISSION_CIO = 'saleshistory';
	const JSONCODE       = 'ci-sales-history';
	const TITLE          = 'CI: Sales History';
	const SUMMARY        = 'View Sales History';
	const SUBFUNCTIONKEY = 'sales-history';

/* =============================================================
	Indexes
============================================================= */
	public static function index(WireData $data) {
		$fields = ['rid|int', 'refresh|bool'];
		self::sanitizeParametersShort($data, $fields);
		self::throw404IfInvalidCustomerOrPermission($data);
		$data->custID = self::getCustidByRid($data->rid);

		if ($data->refresh) {
			self::requestJson(self::prepareJsonRequest($data));
			self::pw('session')->redirect(self::ciSalesHistoryUrl($data->rid), $http301 = false);
		}
		return self::orders($data);
	}

	private static function orders(WireData $data) {
		$json = self::fetchData($data);
		$customer = self::getCustomerByRid($data->rid);

		self::initHooks();
		self::pw('page')->headline = "CI: $customer->name Sales History";

		$html = '';
		$html .= self::displayHistory($data, $customer, $json);
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function ordersUrl($rID, $refreshdata = false) {
		$url = new Purl(self::ciSalesHistoryUrl($rID));

		if ($refreshdata) {
			$url->query->set('refresh', 'true');
		}
		return $url->getUrl();
	}

/* =============================================================
	Data Retrieval
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
		$fields = ['rid|int', 'custID|string', 'date|text', 'sessionID|text'];
		self::sanitizeParametersShort($data, $fields);
		if (empty($data->custID)) {
			$data->custID = self::getCustidByRid($data->rid);
		}
		$rqst = ['CISALESHIST', "CUSTID=$data->custID", "SHIPID=$data->shiptoID", "SALESORDRNBR=", "ITEMID="];
		
		if (empty($data->date) === false) {
			$date = date('Ymd', strtotime($data->date));
			$rqst[] = "DATE=$date";
		}
		return $rqst;
	}

/* =============================================================
	Display
============================================================= */
	protected static function displayHistory(WireData $data, Customer $customer, $json = []) {
		$jsonFetcher  = self::getJsonFileFetcher();

		if (empty($json)) {
			return self::renderJsonNotFoundAlert($data, 'Sales History');
		}

		if ($json['error']) {
			return self::renderJsonError($data, $json);
		}
		$page = self::pw('page');
		$page->refreshurl = self::ordersUrl($data->rid, $refresh=true);
		$page->lastmodified = $jsonFetcher->lastModified(self::JSONCODE);
		return self::renderHistory($data, $customer, $json);
	}

/* =============================================================
	HTML Rendering
============================================================= */
	protected static function renderHistory(WireData $data, Customer $customer, array $json) {
		$formatter = self::getFormatter();
		$docm      = self::getDocFinder();
		return self::pw('config')->twig->render('customers/ci/.new/sales-orders/display.twig', ['customer' => $customer, 'json' => $json, 'formatter' => $formatter, 'blueprint' => $formatter->get_tableblueprint(), 'docm' => $docm]);
	}

/* =============================================================
	Supplemental
============================================================= */
	// NOTE: Keep public, it's used in Ci\PurchaseHistory
	public static function getFormatter() {
		$f = new Formatter();
		$f->init_formatter();
		return $f;
	}

	// NOTE: Keep public, it's used in Ci\PurchaseHistory
	public static function getDocFinder() {
		return new DocFinders\SalesOrder();
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMci');

		$m->addHook('Page(pw_template=ci)::documentListUrl', function($event) {
			$event->return = Documents::documentsUrlSalesorder($event->arguments(0), $event->arguments(1));
		});
	}
}
