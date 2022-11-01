<?php namespace Controllers\Mci\Ci;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Dplus Models
use Customer;
// ProcessWire
use ProcessWire\WireData;

/**
 * Ci\Standing Orders
 * 
 * Handles the CI Standing Orders Page
 */
class StandingOrders extends AbstractSubfunctionController {
	const PERMISSION_CIO = 'standingorders';
	const JSONCODE       = 'ci-standingorders';
	const TITLE          = 'CI: Standing Orders';
	const SUMMARY        = 'View Standing Orders';
	const SUBFUNCTIONKEY = 'credit';

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
			self::pw('session')->redirect(self::ciStandingOrdersUrl($data->rid), $http301 = false);
		}
		return self::credit($data);
	}

	private static function credit(WireData $data) {
		$json = self::fetchData($data);
		$customer = self::getCustomerByRid($data->rid);

		self::initHooks();
		self::pw('page')->headline = "CI: $customer->name Standing Orders";

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

	protected static function prepareJsonRequest(WireData $data) {
		$fields = ['rid|int', 'shiptoID|text'];
		self::sanitizeParametersShort($data, $fields);
		self::decorateInputDataWithCustid($data);
		return ['CISTANDORDR', "CUSTID=$data->custID", "SHIPID=$data->shiptoID"];
	}

/* =============================================================
	4. URLs
============================================================= */
	public static function ordersUrl($rID, $refreshdata = false) {
		$url = new Purl(self::ciStandingOrdersUrl($rID));

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
			return self::renderJsonNotFoundAlert($data, 'Standing Orders');
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
		return self::pw('config')->twig->render('customers/ci/.new/standing-orders/display.twig', ['customer' => $customer, 'json' => $json]);
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
	
}
