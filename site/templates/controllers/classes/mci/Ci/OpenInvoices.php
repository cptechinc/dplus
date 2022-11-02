<?php namespace Controllers\Mci\Ci;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Dplus Models
use Customer;
// ProcessWire
use ProcessWire\WireData;
// Dplus Screen Formatters
use Dplus\ScreenFormatters\Ci\OpenInvoices as Formatter;
// Alias Document Finders
use Dplus\DocManagement\Finders as DocFinders;

/**
 * Ci\OpenInvoices
 * 
 * Handles the CI Open Invoices Page
 */
class OpenInvoices extends AbstractSubfunctionController {
	const PERMISSION_CIO = 'openinvoices';
	const JSONCODE       = 'ci-openinvoices';
	const TITLE          = 'Open Invoices';
	const SUMMARY        = 'View Open Invoices';
	const SUBFUNCTIONKEY = 'open-invoices';

/* =============================================================
	1. Indexes
============================================================= */
	public static function index(WireData $data) {
		$fields = ['rid|int', 'refresh|bool'];
		self::sanitizeParametersShort($data, $fields);
		self::throw404IfInvalidCustomerOrPermission($data);
		self::decorateInputDataWithCustid($data);
		self::decoratePageWithCustid($data);

		if ($data->refresh) {
			self::requestJson(self::prepareJsonRequest($data));
			self::pw('session')->redirect(self::ciOpenInvoicesUrl($data->rid), $http301 = false);
		}
		return self::invoices($data);
	}

	private static function invoices(WireData $data) {
		$json = self::fetchData($data);
		$customer = self::getCustomerByRid($data->rid);

		self::initHooks();
		self::pw('page')->headline = "CI: $customer->name Open Invoices";

		$html = '';
		$html .= self::displayInvoices($data, $customer, $json);
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

	protected static function prepareJsonRequest(WireData $data) {
		$fields = ['rid|int', 'sessionID|text'];
		self::sanitizeParametersShort($data, $fields);
		self::decorateInputDataWithCustid($data);
		return ['CIOPENINV', "CUSTID=$data->custID"];
	}

/* =============================================================
	4. URLs
============================================================= */
	public static function ordersUrl($rID, $refreshdata = false) {
		$url = new Purl(self::ciOpenInvoicesUrl($rID));

		if ($refreshdata) {
			$url->query->set('refresh', 'true');
		}
		return $url->getUrl();
	}

/* =============================================================
	5. Displays
============================================================= */
	protected static function displayInvoices(WireData $data, Customer $customer, $json = []) {
		if (empty($json)) {
			return self::renderJsonNotFoundAlert($data, 'Open Invoices');
		}

		if ($json['error']) {
			return self::renderJsonError($data, $json);
		}
		self::addPageData($data);
		return self::renderInvoices($data, $customer, $json);
	}

/* =============================================================
	6. HTML Rendering
============================================================= */
	protected static function renderInvoices(WireData $data, Customer $customer, array $json) {
		$formatter = self::getFormatter();
		$docm      = self::getDocFinder();
		return self::pw('config')->twig->render('customers/ci/.new/open-invoices/display.twig', ['customer' => $customer, 'json' => $json, 'formatter' => $formatter, 'blueprint' => $formatter->get_tableblueprint(), 'docm' => $docm]);
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
