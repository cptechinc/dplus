<?php namespace Controllers\Mci\Ci;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Dplus Models
use Customer;
// ProcessWire
use ProcessWire\WireData;
// Dplus Screen Formatters
use Dplus\ScreenFormatters\Ci\Payments as Formatter;
// Alias Document Finders
use Dplus\DocManagement\Finders as DocFinders;

/**
 * Ci\Payments
 * 
 * Handles the CI Payments Page
 */
class Payments extends AbstractSubfunctionController {
	const PERMISSION_CIO = 'payments';
	const JSONCODE       = 'ci-payments';
	const TITLE          = 'Payments';
	const SUMMARY        = 'View Payments';
	const SUBFUNCTIONKEY = 'payments';

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
			self::pw('session')->redirect(self::ciPaymentsUrl($data->rid), $http301 = false);
		}
		return self::payments($data);
	}

	private static function payments(WireData $data) {
		$json = self::fetchData($data);
		$customer = self::getCustomerByRid($data->rid);

		self::initHooks();
		self::pw('page')->headline = "CI: $customer->name Payments";

		$html = '';
		$html .= self::displayPayments($data, $customer, $json);
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
		return self::paymentsUrl($data->rid, $refresh=true);
	}

	protected static function prepareJsonRequest(WireData $data) {
		$fields = ['rid|int'];
		self::sanitizeParametersShort($data, $fields);
		self::decorateInputDataWithCustid($data);
		return ['CIPAYMENT', "CUSTID=$data->custID"];
	}

/* =============================================================
	4. URLs
============================================================= */
	public static function paymentsUrl($rID, $refreshdata = false) {
		$url = new Purl(self::ciPaymentsUrl($rID));

		if ($refreshdata) {
			$url->query->set('refresh', 'true');
		}
		return $url->getUrl();
	}

/* =============================================================
	5. Displays
============================================================= */
	protected static function displayPayments(WireData $data, Customer $customer, $json = []) {
		if (empty($json)) {
			return self::renderJsonNotFoundAlert($data, 'Payments');
		}

		if ($json['error']) {
			return self::renderJsonError($data, $json);
		}
		self::addPageData($data);
		return self::renderPayments($data, $customer, $json);
	}

/* =============================================================
	6. HTML Rendering
============================================================= */
	protected static function renderPayments(WireData $data, Customer $customer, array $json) {
		$formatter = self::getFormatter();
		$docm      = self::getDocFinder();
		return self::pw('config')->twig->render('customers/ci/.new/payments/display.twig', ['customer' => $customer, 'json' => $json, 'formatter' => $formatter, 'blueprint' => $formatter->get_tableblueprint(), 'docm' => $docm]);
	}

/* =============================================================
	7. Class / Module Getting
============================================================= */
	public static function getFormatter() {
		$f = new Formatter();
		$f->init_formatter();
		return $f;
	}

	public static function getDocFinder() {
		return new DocFinders\Ar();
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
			$event->return = Documents::documentsUrlPayment($event->arguments(0), $event->arguments(1), $event->arguments(2));
		});
	}
}
