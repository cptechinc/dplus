<?php namespace Controllers\Mci\Ci;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Dplus Models
use Customer;
// ProcessWire
use ProcessWire\WireData;

/**
 * Ci\Credit
 * 
 * Handles the CI Credit Page
 */
class Credit extends AbstractSubfunctionController {
	const PERMISSION_CIO = 'credit';
	const JSONCODE       = 'ci-credit';
	const TITLE          = 'Credit';
	const SUMMARY        = 'View Credit';
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
			$id = self::pw('config')->ci->useRid ? $data->rid : $data->custID;
			self::pw('session')->redirect(self::ciCreditUrl($id), $http301 = false);
		}
		return self::credit($data);
	}

	private static function credit(WireData $data) {
		$json = self::fetchData($data);
		$customer = self::getCustomerFromWireData($data);

		self::initHooks();
		self::pw('page')->headline = "CI: $customer->name Credit";

		$html = '';
		$html .= self::displayCredit($data, $customer, $json);
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
		return self::creditUrl($id, $refresh=true);
	}

	protected static function prepareJsonRequest(WireData $data) {
		$fields = ['rid|int', 'sessionID|text'];
		self::sanitizeParametersShort($data, $fields);
		self::decorateInputDataWithCustid($data);
		return ['CICREDIT', "CUSTID=$data->custID"];
	}

/* =============================================================
	4. URLs
============================================================= */
	public static function creditUrl($rID, $refreshdata = false) {
		$url = new Purl(self::ciCreditUrl($rID));

		if ($refreshdata) {
			$url->query->set('refresh', 'true');
		}
		return $url->getUrl();
	}

/* =============================================================
	5. Displays
============================================================= */
	protected static function displayCredit(WireData $data, Customer $customer, $json = []) {
		self::addPageData($data);
		
		if (empty($json)) {
			return self::renderJsonNotFoundAlert($data, 'Credit');
		}

		if ($json['error']) {
			return self::renderJsonError($data, $json);
		}
		return self::renderCredit($data, $customer, $json);
	}

/* =============================================================
	6. HTML Rendering
============================================================= */
	protected static function renderCredit(WireData $data, Customer $customer, array $json) {
		return self::pw('config')->twig->render('customers/ci/.new/credit/display.twig', ['customer' => $customer, 'json' => $json]);
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
