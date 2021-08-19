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
// Dplus CRUD
use Dplus\Mci\Ci\Contact\Edit as ContactCRUD;
// Mvc Controllers
use Mvc\Controllers\AbstractController;
use Controllers\Mci\Ci\Base;
use Controllers\Mci\Ci\Shipto;

class Edit extends Base {

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['custID|text', 'shiptoID|text', 'q|text'];
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

	public static function handleCRUD($data) {
		$fields = ['custID|text', 'shiptoID|text', 'contactID|text', 'name|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$url = self::ciContactEditUrl($data->custID, $data->shiptoID, $data->contactID);

		if (empty($data->action) === false) {
			$crud = new ContactCRUD();
			$crud->processInput(self::pw('input'));

			switch ($data->action) {
				case 'update-contact':
					if ($data->name != '' && $data->contactID != $data->name) {
						$url = self::ciContactEditUrl($data->custID, $data->shiptoID, $data->name);
					}
					break;
			}
		}
		self::pw('session')->redirect($url, $http301 = false);
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
		$html .= $config->twig->render('customers/ci/contact/edit/display.twig', ['contact' => $contact, 'customer' => self::getCustomer($data->custID)]);
		return $html;
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getContact($custID, $shiptoID, $contactID) {
		$m = new ContactCRUD();
		return $m->contact($custID, $shiptoID, $contactID);
	}

	public static function exists($custID, $shiptoID, $contactID) {
		$m = new ContactCRUD();
		return $m->exists($custID, $shiptoID, $contactID);
	}
}
