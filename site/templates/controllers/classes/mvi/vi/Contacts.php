<?php namespace Controllers\Mvi\Vi;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use VendorQuery, Vendor;
use VendorShipfromQuery, VendorShipfrom;
// ProcessWire Classes, Modules
use ProcessWire\Page;
use ProcessWire\WireData;
// Dplus Filters
use Dplus\Filters;
// Dplus Configs
use Dplus\Configs;
// Mvc Controllers
use Mvc\Controllers\Controller;

class Contacts extends Base {
/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['vendorID|string', 'shipfromID|text', 'q|text'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateVendorid($data->vendorID) === false) {
			self::pw('session')->redirect(self::viUrl(), $http301 = false);
		}

		if (self::validateVendoridPermission($data) === false) {
			return self::displayInvalidVendorOrPermissions($data);
		}

		if (empty($data->shipfromID) === false && Shipfrom::validateVendorShipfromid($data->vendorID, $data->shipfromID)) {
			return self::contactsShipfrom($data);
		}

		return self::contacts($data);
	}

	private static function contacts($data) {
		$vendor = self::getVendor($data->vendorID);
		self::pw('page')->headline = "VI: $vendor->name Contacts";
		return self::displayContacts($data, $vendor);
	}

	private static function contactsShipfrom($data) {
		$shipfrom = Shipfrom::getShipfrom($data->vendorID, $data->shipfromID);
		self::pw('page')->headline = "VI: $shipfrom->vendorid Ship-From $shipfrom->name Contacts";
		return self::displayContactsShipfrom($data, $shipfrom);
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayContacts($data, Vendor $vendor) {
		$config = self::pw('config');

		$html = '';
		$html .= self::displayBreadCrumbs($data);
		$html .= $config->twig->render('vendors/vi/contacts/display.twig', ['vendor' => $vendor]);
		return $html;
	}

	private static function displayContactsShipfrom($data, VendorShipfrom $shipfrom) {
		$config = self::pw('config');

		$html = '';
		$html .= self::displayBreadCrumbs($data);
		$html .= $config->twig->render('vendors/vi/contacts/ship-from/display.twig', ['shipfrom' => $shipfrom]);
		return $html;
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {

	}

/* =============================================================
	Supplemental
============================================================= */
}
