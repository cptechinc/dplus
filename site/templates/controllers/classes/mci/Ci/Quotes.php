<?php namespace Controllers\Mci\Ci;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Dplus Models
use Customer;
// ProcessWire
use ProcessWire\WireData;
// Dplus Screen Formatters
use Dplus\ScreenFormatters\Ci\Quotes as Formatter;
// Alias Document Finders
use Dplus\DocManagement\Finders as DocFinders;

/**
 * Ci\Quotes
 * 
 * Handles the CI Quotes Page
 */
class Quotes extends AbstractSubfunctionController {
	const PERMISSION_CIO = 'quotes';
	const JSONCODE       = 'ci-quotes';
	const TITLE          = 'Quotes';
	const SUMMARY        = 'View Quotes';
	const SUBFUNCTIONKEY = 'quotes';

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
			$id = self::pw('config')->ci->useRid ? $data->rid : $data->custID;
			self::pw('session')->redirect(self::ciQuotesUrl($id), $http301 = false);
		}
		return self::quotes($data);
	}

	private static function quotes(WireData $data) {
		$json = self::fetchData($data);
		$customer = self::getCustomerFromWireData($data);

		self::initHooks();
		self::pw('page')->headline = "CI: $customer->name Quotes";

		$html = '';
		$html .= self::displayQuotes($data, $customer, $json);
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
		$id = self::pw('config')->ci->useRid ? $data->rid : $data->custID;
		return self::quotesUrl($id, $refresh=true);
	}

	protected static function prepareJsonRequest(WireData $data) {
		$fields = ['rid|int', 'sessionID|text'];
		self::sanitizeParametersShort($data, $fields);
		self::decorateInputDataWithCustid($data);
		return ['CIQUOTE', "CUSTID=$data->custID"];
	}

/* =============================================================
	4. URLs
============================================================= */
	public static function quotesUrl($rID, $refreshdata = false) {
		$url = new Purl(self::ciQuotesUrl($rID));

		if ($refreshdata) {
			$url->query->set('refresh', 'true');
		}
		return $url->getUrl();
	}

/* =============================================================
	5. Displays
============================================================= */
	protected static function displayQuotes(WireData $data, Customer $customer, $json = []) {
		self::addPageData($data);

		if (empty($json)) {
			return self::renderJsonNotFoundAlert($data, 'Quotes');
		}

		if ($json['error']) {
			return self::renderJsonError($data, $json);
		}
		return self::renderQuotes($data, $customer, $json);
	}

/* =============================================================
	6. HTML Rendering
============================================================= */
	protected static function renderQuotes(WireData $data, Customer $customer, array $json) {
		$formatter = self::getFormatter();
		$docm      = self::getDocFinder();
		return self::pw('config')->twig->render('customers/ci/.new/quotes/display.twig', ['customer' => $customer, 'json' => $json, 'formatter' => $formatter, 'blueprint' => $formatter->get_tableblueprint(), 'docm' => $docm]);
	}

/* =============================================================
	7. Class / Module Getting
============================================================= */
	private static function getFormatter() {
		$f = new Formatter();
		$f->init_formatter();
		return $f;
	}

	private static function getDocFinder() {
		return new DocFinders\Qt();
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
			$event->return = Documents::documentsUrlQuote($event->arguments(0), $event->arguments(1));
		});
	}
}
