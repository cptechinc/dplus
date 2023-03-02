<?php namespace Controllers\Mci\Ci;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Dplus Models
use Customer;
// ProcessWire
use ProcessWire\Page;
use ProcessWire\WireData;
// Dplus Screen Formatters
use Dplus\ScreenFormatters\Ci\SalesHistory as Formatter;
// Alias Document Finders
use Dplus\DocManagement\Finders as DocFinders;

/**
 * Ci\Sales History
 * 
 * Handles the CI Sales History Page
 */
class SalesHistory extends AbstractSubfunctionController {
	const PERMISSION_CIO = 'saleshistory';
	const JSONCODE       = 'ci-sales-history';
	const TITLE          = 'Sales History';
	const SUMMARY        = 'View Sales History';
	const SUBFUNCTIONKEY = 'sales-history';
	const DATE_FORMAT_DISPLAY = 'm/d/Y';
	const DATE_FORMAT_REQUEST = 'Ymd';

/* =============================================================
	1. Indexes
============================================================= */
	public static function index(WireData $data) {
		$fields = ['rid|int', 'date|text', 'refresh|bool'];
		self::sanitizeParametersShort($data, $fields);
		self::throw404IfInvalidCustomerOrPermission($data);
		self::decorateInputDataWithCustid($data);
		self::decoratePageWithCustid($data);

		if ($data->refresh) {
			self::requestJson(self::prepareJsonRequest($data));
			self::pw('session')->redirect(self::historyUrl($data->rid, $data->date), $http301 = false);
		}

		if (empty($data->date)) {
			return self::selectDate($data);
		}
		return self::orders($data);
	}

	private static function selectDate(WireData $data) {
		$customer = self::getCustomerFromWireData($data);
		self::pw('page')->headline = "CI: $customer->name Sales History";
		self::addCioStartDate();
		return self::displayDateForm($data);
	}

	private static function orders(WireData $data) {
		$json = self::fetchData($data);
		$customer = self::getCustomerFromWireData($data);

		self::initHooks();
		self::pw('page')->headline = "CI: $customer->name Sales History";

		$html = '';
		$html .= self::displayHistory($data, $customer, $json);
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
		return self::historyUrl($data->rid, $data->date, $refresh=true);
	}

	protected static function fetchData(WireData $data) {
		$jsonFetcher = self::getJsonFileFetcher();
		if ($jsonFetcher->exists(self::JSONCODE) && empty(self::getSessionVar('custpo')) === false) {
			$jsonFetcher->delete(self::JSONCODE);
			self::deleteSessionVar('custpo');
		}
		return parent::fetchData($data);
	}

	protected static function prepareJsonRequest(WireData $data) {
		$fields = ['rid|int', 'date|text', 'sessionID|text'];
		self::sanitizeParametersShort($data, $fields);
		self::decorateInputDataWithCustid($data);
		$rqst = ['CISALESHIST', "CUSTID=$data->custID", "SHIPID=$data->shiptoID", "SALESORDRNBR=", "ITEMID="];
		
		if (empty($data->date) === false) {
			$date = date('Ymd', strtotime($data->date));
			$rqst[] = "DATE=$date";
		}
		return $rqst;
	}

/* =============================================================
	4. URLs
============================================================= */
	public static function historyUrl($rID, $date = '', $refreshdata = false) {
		$url = new Purl(self::ciSalesHistoryUrl($rID));

		if ($date) {
			$url->query->set('date', $date);
		}

		if ($refreshdata) {
			$url->query->set('refresh', 'true');
		}
		return $url->getUrl();
	}

/* =============================================================
	5. Displays
============================================================= */
	protected static function displayDateForm(WireData $data) {
		return self::renderDateForm($data);
	}

	protected static function displayHistory(WireData $data, Customer $customer, $json = []) {
		if (empty($json)) {
			return self::renderJsonNotFoundAlert($data, 'Sales History');
		}

		if ($json['error']) {
			return self::renderJsonError($data, $json);
		}
		self::addPageData($data);
		return self::renderHistory($data, $customer, $json);
	}

/* =============================================================
	6. HTML Rendering
============================================================= */
	protected static function renderDateForm(WireData $data) {
		return self::pw('config')->twig->render('customers/ci/.new/sales-history/select-date/display.twig', []);
	}

	protected static function renderHistory(WireData $data, Customer $customer, array $json) {
		$formatter = self::getFormatter();
		$docm      = self::getDocFinder();
		return self::pw('config')->twig->render('customers/ci/.new/sales-history/display.twig', ['customer' => $customer, 'json' => $json, 'formatter' => $formatter, 'blueprint' => $formatter->get_tableblueprint(), 'docm' => $docm]);
	}

/* =============================================================
	7. Class / Module Getting
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

		$m->addHook('Page(pw_template=ci)::ciSalesHistoryUrl', function($event) {
			$event->return = self::historyUrl($event->arguments(0));
		});
	}

	/**
	 * Add CIO Configured Start Date for Date Form
	 * @param  Page|null $page
	 * @return bool
	 */
	private static function addCioStartDate(Page $page = null) {
		$page = $page ? $page : self::pw('page');
		$cio = self::getCio();
		$cioUser = $cio->usercio(self::pw('user')->loginid);
		
		if ($cioUser->dayssaleshistory > 0) {
			$page->cioStartDate = date(self::DATE_FORMAT_DISPLAY, strtotime("-$cioUser->dayssaleshistory days"));
			return true;
		}

		if ($cioUser->datesaleshistory) {
			$page->cioStartDate = date(self::DATE_FORMAT_DISPLAY, strtotime($cioUser->datesaleshistory));
			return true;
		}
		$page->cioStartDate = '';
		return true;
	}
}
