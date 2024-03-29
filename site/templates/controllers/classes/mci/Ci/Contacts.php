<?php namespace Controllers\Mci\Ci;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Dplus Models
use Customer;
// ProcessWire
use ProcessWire\WireData;
use ProcessWire\Wire404Exception;

/**
 * Ci\Contacts\Contact
 * 
 * Handles Ci Contacts Page
 */
class Contacts extends AbstractSubfunctionController {
	const PERMISSION_CIO = 'contacts';
	const TITLE      = 'Contacts';
	const SUMMARY    = 'View Customer Contacts';
	const JSONCODE   = 'ci-contacts';
	const SUBFUNCTIONKEY = 'contacts';
	
/* =============================================================
	1. Indexes
============================================================= */
	public static function index(WireData $data) {
		$fields = ['rid|int', 'custID|string', 'shiptoID|text', 'refresh|bool'];
		self::sanitizeParametersShort($data, $fields);
		self::throw404IfInvalidCustomerOrPermission($data);

		self::decorateInputDataWithCustid($data);
		self::decoratePageWithCustid($data);

		if (empty($data->shiptoID) === false && Shipto::validateShiptoAccess($data->custID, $data->shiptoID) === false) {
			throw new Wire404Exception();
		}

		if ($data->refresh) {
			self::requestJson(self::prepareJsonRequest($data));
			$id = self::pw('config')->ci->useRid ? $data->rid : $data->custID;
			self::pw('session')->redirect(self::contactsUrl($id, $data->shiptoID), $http301=false);
		}
		return self::contacts($data);
	}

	private static function contacts(WireData $data) {
		$json = self::fetchData($data);
		$customer = self::getCustomerFromWireData($data);
		self::pw('page')->headline = "CI: $customer->name Contacts";

		if (empty($data->shiptoID) === false) {
			self::pw('page')->headline = "CI: $customer->name - $data->shiptoID Contacts";
		}

		$html = '';
		$html .= self::displayContacts($data, $customer, $json);
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
		return self::contactsUrl($id, $data->shiptoID, $refresh=true);
	}

	/**
	 * Return if JSON Data matches for this Customer ID
	 * @param  WireData $data
	 * @param  array    $json
	 * @return bool
	 */
	protected static function validateJsonFileMatches(WireData $data, array $json) {
		if ($json['custid'] != $data->custID) {
			return false;
		}
		if (array_key_exists('shipid', $json) && $json['shipid'] != $data->shiptoID) {
			return false;
		}
		return true;
	}

	protected static function prepareJsonRequest(WireData $data) {
		$fields = ['rid|int', 'shiptoID|text', 'custID|string', 'sessionID|text'];
		self::sanitizeParametersShort($data, $fields);
		self::decorateInputDataWithCustid($data);
		return ['CICONTACT', "CUSTID=$data->custID", "SHIPID=$data->shiptoID"];
	}

/* =============================================================
	4. URLs
============================================================= */
	public static function contactsUrl($rID, $shiptoID = '', $refreshdata = false) {
		$url = new Purl(self::ciContactsUrl($rID, $shiptoID));
		if ($refreshdata) {
			$url->query->set('refresh', 'true');
		}
		return $url->getUrl();
	}

/* =============================================================
	5. Displays
============================================================= */
	protected static function displayContacts(WireData $data, Customer $customer, $json = []) {
		if (empty($json)) {
			self::addPageData($data);
			return self::renderJsonNotFoundAlert($data, 'Contacts');
		}

		if ($json['error']) {
			self::addPageData($data);
			return self::renderJsonError($data, $json);
		}
		self::addPageData($data);
		return self::renderContacts($data, $customer, $json);
	}

/* =============================================================
	6. HTML Rendering
============================================================= */
	private static function renderContacts(WireData $data, Customer $customer, array $json) {
		return self::pw('config')->twig->render('customers/ci/.new/contacts/display.twig', ['customer' => $customer, 'json' => $json]);
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
