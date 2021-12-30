<?php namespace Controllers\Mvi\Vi;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use VendorShipfromQuery, VendorShipfrom;
// ProcessWire Classes, Modules
use ProcessWire\Page;
use ProcessWire\WireData;
// Dplus Filters
use Dplus\Filters;
// Dplus Configs
use Dplus\Configs;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Shipfrom extends Base {
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

		if (empty($data->shipfromID) === false) {
			return self::shipfrom($data);
		}
		return self::list($data);
	}

	private static function list($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		$filter = new Filters\Map\VendorShipfrom();
		$filter->vendorid($data->vendorID);
		// Redirect if there is only 1
		if ($filter->query->count() == 1) {
			self::pw('session')->redirect(self::viShipfromUrl($data->vendorID, $filter->query->findOne()->id), $http301 = false);
		}

		$filter->sortby(self::pw('page'));

		if ($data->q) {
			$data->q = strtoupper($data->q);

			if ($filter->exists($data->q)) {
				self::pw('session')->redirect(self::viUrl($data->q), $http301 = false);
			}

			$filter->search($data->q);
			self::pw('page')->headline = "VI: Searching Ship-Froms for '$data->q'";
		}
		$shipfroms = $filter->query->paginate(self::pw('input')->pageNum, 10);
		return self::displayList($data, $shipfroms);
	}

	private static function shipfrom($data) {
		if (self::validateVendorShipfromid($data->vendorID, $data->shipfromID) === false) {
			return self::displayInvalidShipfromid($data);
		}

		$shipfrom = self::getShipfrom($data->vendorID, $data->shipfromID);
		$page     = self::pw('page');
		$page->show_breadcrumbs = false;

		$page->headline = "VI: {$shipfrom->vendorid} Ship-From $shipfrom->name";
		$apData = self::getApData($shipfrom);
		return self::displayShipfrom($data, $shipfrom, $apData);
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $shipfroms) {
		$config = self::pw('config');

		$html = '';
		$html .= $config->twig->render('vendors/vi/ship-froms/search.twig', ['shipfroms' => $shipfroms, 'q' => $data->q]);
		return $html;
	}

	protected static function displayInvalidVendorShipfromid($data) {
		return self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Vendor $data->vendorID Ship-From $data->shipfromID not found"]);
	}

	private static function displayShipfrom($data, VendorShipfrom $shipfrom, WireData $apData) {
		$config = self::pw('config');

		$html = '';
		$html .= self::displayBreadCrumbs($data);
		$html .= $config->twig->render('vendors/vi/ship-froms/ship-from/display.twig', ['shipfrom' => $shipfrom, 'apData' => $apData]);
		return $html;
	}

	private static function getApData(VendorShipfrom $shipfrom) {
		$data = new WireData();

		$filter = new Filters\Mpo\PurchaseOrder();
		$filter->vendorid($shipfrom->vendorid);
		$filter->shipfromid($shipfrom->id);
		$filter->query->limit(10);
		$data->orders = $filter->query->paginate(1, 10);
		return $data;
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {

	}

/* =============================================================
	Supplemental
============================================================= */
	public static function validateVendorShipfromid($vendorID, $shipfromID) {
		$validate = self::getValidator();
		return $validate->vendorShipfromid($vendorID, $shipfromID);
	}

	public static function getShipfrom($vendorID, $shipfromID) {
		$q = VendorShipfromQuery::create();
		$q->filterByVendorid($vendorID);
		$q->filterByShipfromid($shipfromID);
		return $q->findOne();
	}
}
