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
	const TITLE      = 'CI: Contacts';
	const SUMMARY    = 'View Customer Contacts';
	const JSONCODE   = 'ci-contacts';
	const SUBFUNCTIONKEY = 'contacts';
	
/* =============================================================
	Indexes
============================================================= */
	public static function index(WireData $data) {
		$fields = ['rid|int', 'shiptoID|text', 'refresh|bool'];
		self::sanitizeParametersShort($data, $fields);
		self::throw404IfInvalidCustomerOrPermission($data);

		$data->custID = self::getCustidByRid($data->rid);
		self::pw('page')->custid = $data->custID;

		if (empty($data->shiptoID) === false && Shipto::validateShiptoAccess($data->custID, $data->shiptoID) === false) {
			throw new Wire404Exception();
		}

		if ($data->refresh) {
			self::requestJson(self::prepareJsonRequest($data));
			self::pw('session')->redirect(self::contactsUrl($data->rid, $data->shiptoID), $http301=false);
		}
		return self::contacts($data);
	}

	private static function contacts(WireData $data) {
		$json = self::fetchData($data);
		$customer = self::getCustomerByRid($data->rid);
		self::pw('page')->custid   = $customer->id;
		self::pw('page')->headline = "CI: $customer->name Contacts";

		if (empty($data->shiptoID) === false) {
			self::pw('page')->headline = "CI: $customer->name - $data->shiptoID Contacts";
		}

		$html = '';
		$html .= self::displayContacts($data, $customer, $json);
		return $html;
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
		return self::contactsUrl($data->rid, $data->shiptoID, $refresh=true);
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

/* =============================================================
	Display
============================================================= */
	protected static function displayContacts(WireData $data, Customer $customer, $json = []) {
		$jsonFetcher   = self::getJsonFileFetcher();
		if (empty($json)) {
			return self::renderJsonNotFoundAlert($data, 'Contacts');
		}

		if ($json['error']) {
			return self::renderJsonError($data, $json);
		}
		$page = self::pw('page');
		$page->refreshurl   = self::contactsUrl($data->rid, $data->shiptoID, $refresh=true);
		$page->lastmodified = $jsonFetcher->lastModified(self::JSONCODE);
		return self::renderContacts($data, $customer, $json);
	}

/* =============================================================
	Render HTML
============================================================= */
	private static function renderContacts(WireData $data, Customer $customer, array $json) {
		return self::pw('config')->twig->render('customers/ci/.new/contacts/display.twig', ['customer' => $customer, 'json' => $json]);
	}
	

/* =============================================================
	URLs
============================================================= */
	public static function contactsUrl($rID, $shiptoID = '', $refreshdata = false) {
		$url = new Purl(self::ciContactsUrl($rID, $shiptoID));
		if ($refreshdata) {
			$url->query->set('refresh', 'true');
		}
		return $url->getUrl();
	}

/* =============================================================
	Data Requests
============================================================= */
	protected static function prepareJsonRequest(WireData $data) {
		$fields = ['rid|int', 'shiptoID|text', 'custID|string', 'sessionID|text'];
		self::sanitizeParametersShort($data, $fields);
		if (empty($data->custID)) {
			$data->custID = self::getCustidByRid($data->rid);
		}
		return ['CICONTACT', "CUSTID=$data->custID", "SHIPID=$data->shiptoID"];
	}
}
