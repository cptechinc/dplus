<?php namespace Controllers\Mci\Ci\Contacts;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use CustomerQuery, Customer;
use CustomerShiptoQuery, CustomerShipto;
// Dpluso Model
use CustindexQuery, Custindex;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Dplus Validators
use Dplus\CodeValidators\Mar as MarValidator;
// Dplus Filters
use Dplus\Filters;
// Mvc Controllers
use Mvc\Controllers\Controller;
use Controllers\Mci\Ci\Base;
use Controllers\Mci\Ci\Shipto;

class Contact extends Base {

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['custID|string', 'shiptoID|text', 'q|text'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateCustidPermission($data) === false) {
			return self::displayInvalidCustomerOrPermissions($data);
		}

		if (empty($data->shiptoID) === false) {
			if (Shipto::validateShiptoAccess($data) === false) {
				return Shipto::displayInvalidShiptoOrPermissions($data);
			}
		}
		return self::contact($data);
	}

	private static function contact($data) {
		self::pw('page')->headline = "CI: Contact";
		if (self::exists($data->custID, $data->shiptoID, $data->contactID) === false) {
			return self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Contact Not Found', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "$data->custID $data->shiptID $data->contactID not found"]);
		}
		$contact = self::getContact($data->custID, $data->shiptoID, $data->contactID);
		self::pw('page')->headline = "CI: $data->custID Contact $data->contactID";
		return self::displayContact($data, $contact);
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayContact($data, Custindex $contact) {
		$config = self::pw('config');

		$html = '';
		$html .= self::displayBreadCrumbs($data);
		$html .= $config->twig->render('customers/ci/contact/display.twig', ['contact' => $contact, 'customer' => self::getCustomer($data->custID)]);
		return $html;
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getContact($custID, $shiptoID, $contactID) {
		$q = CustIndexQuery::create();
		$q->filterByCustid($custID)->filterByShiptoid($shiptoID)->filterByContact($contactID);
		return $q->findOne();
	}

	public static function exists($custID, $shiptoID, $contactID) {
		$q = CustIndexQuery::create();
		$q->filterByCustid($custID)->filterByShiptoid($shiptoID)->filterByContact($contactID);
		return boolval($q->count());
	}
}
