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
// Dplus Qnotes
use Dplus\Qnotes\Vend as Qnotes;
// Mvc Controllers
use Mvc\Controllers\Controller;

class Notes extends Base {
/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['vendorID|text', 'shipfromID|text', 'q|text'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateVendorid($data->vendorID) === false) {
			self::pw('session')->redirect(self::viUrl(), $http301 = false);
		}

		if (self::validateVendoridPermission($data) === false) {
			return self::displayInvalidVendorOrPermissions($data);
		}

		return self::notes($data);
	}

	private static function notes($data) {
		$vendor = self::getVendor($data->vendorID);
		self::pw('page')->headline = "VI: $vendor->name Notes";
		$qnotes = self::getNotes($data);
		return self::displayNotes($data, $vendor, $qnotes);
	}

	private static function getNotes($data) {
		$order = new WireData();
		$order->notes = Qnotes\Order::getInstance()->getNotesArray($data->vendorID, $data->shipfromID);
		$order->cols  = Qnotes\Order::getInstance()->fieldAttribute('note', 'cols');

		$internal = new WireData();
		$internal->notes = Qnotes\Internal::getInstance()->getNotesArray($data->vendorID, $data->shipfromID);
		$internal->cols  = Qnotes\Internal::getInstance()->fieldAttribute('note', 'cols');

		$qnotes = new WireData();
		$qnotes->order    = $order;
		$qnotes->internal = $internal;
		return $qnotes;
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayNotes($data, Vendor $vendor, WireData $qnotes) {
		$config = self::pw('config');

		$html = '';
		$html .= self::displayBreadCrumbs($data);
		$html .= $config->twig->render('vendors/vi/notes/new/display.twig', ['vendor' => $vendor, 'qnotes' => $qnotes]);
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
