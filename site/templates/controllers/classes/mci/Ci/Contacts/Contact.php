<?php namespace Controllers\Mci\Ci\Contacts;
// Dplus Model
use Customer;
// Dpluso Model
use CustindexQuery, Custindex;
// ProcessWire
use ProcessWire\WireData;
use ProcessWire\Wire404Exception;
// Mvc Controllers
use Controllers\Mci\Ci\Shipto;
use Controllers\Mci\Ci\AbstractSubfunctionController;

/**
 * Ci\Contacts\Contact
 * 
 * Handles Ci Contact Page
 */
class Contact extends AbstractSubfunctionController {
	const TITLE      = 'Contact';
	const SUMMARY    = 'View Customer Contact';

/* =============================================================
	Indexes
============================================================= */
	public static function index(WireData $data) {
		$fields = ['rid|int', 'shiptoID|text', 'contactID|string', 'q|text'];
		self::sanitizeParametersShort($data, $fields);
		self::throw404IfInvalidCustomerOrPermission($data);

		$data->custID = self::getCustidByRid($data->rid);
		self::pw('page')->custid = $data->custID;

		if (empty($data->shiptoID) === false && Shipto::validateShiptoAccess($data->custID, $data->shiptoID) === false) {
			throw new Wire404Exception();
		}
		return static::contact($data);
	}

	protected static function contact(WireData $data) {
		self::pw('page')->headline = "CI: Contact";
		if (self::exists($data->custID, $data->shiptoID, $data->contactID) === false) {
			throw new Wire404Exception();
		}
		$contact = self::getContact($data->custID, $data->shiptoID, $data->contactID);
		self::pw('page')->headline = "CI: $data->custID Contact $data->contactID";
		return static::displayContact($data, $contact);
	}

/* =============================================================
	Data Fetching
============================================================= */
	/**
	 * Return Contact
	 * @param  string $custID
	 * @param  string $shiptoID
	 * @param  string $contactID
	 * @return Custindex
	 */
	public static function getContact($custID, $shiptoID, $contactID) {
		$q = CustindexQuery::create();
		$q->filterByCustid($custID)->filterByShiptoid($shiptoID)->filterByContact($contactID);
		return $q->findOne();
	}

	/**
	 * Return if Contact exists
	 * @param  string $custID
	 * @param  string $shiptoID
	 * @param  string $contactID
	 * @return bool
	 */
	public static function exists($custID, $shiptoID, $contactID) {
		$q = CustindexQuery::create();
		$q->filterByCustid($custID)->filterByShiptoid($shiptoID)->filterByContact($contactID);
		return boolval($q->count());
	}

/* =============================================================
	Displays
============================================================= */
	protected static function displayContact(WireData $data, Custindex $contact) {
		$html = '';
		$html .= static::renderContact($data, $contact);
		return $html;
	}

/* =============================================================
	HTML Rendering
============================================================= */
	protected static function renderContact(WireData $data, Custindex $contact) {
		$customer = self::getCustomerFromWireData($data);
		return self::pw('config')->twig->render('customers/ci/.new/contact/display.twig', ['contact' => $contact, 'customer' => $customer]);
	}
}
