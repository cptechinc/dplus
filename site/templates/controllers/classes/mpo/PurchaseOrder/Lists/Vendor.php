<?php namespace Controllers\Mpo\PurchaseOrder\Lists;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager as ModelPager;
// Dplus Model
use PurchaseOrderQuery, PurchaseOrder as PoModel;
use VendorQuery, Vendor as VendorModel;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\WireData;
// Dplus Configs
use Dplus\Configs;
// Dplus Filters
use Dplus\Filters;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Mvc Controllers
use Controllers\Mpo\PurchaseOrder\Lists\PurchaseOrder as PoList;

class Vendor extends PoList {
/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['vendorID|string'];
		self::sanitizeParametersShort($data, $fields);
		$validate = new Validators\Map();

		if ($validate->vendorid($data->vendorID) === false) {
			self::pw('page')->js .= self::pw('config')->twig->render('purchase-orders/list.js.twig');
			return self::vendorForm($data);
		}
		return self::list($data);
	}

	public static function list($data) {
		$validate = new Validators\Map();
		if ($validate->vendorid($data->vendorID) === false) {
			self::pw('page')->js .= self::pw('config')->twig->render('purchase-orders/list.js.twig');
			return self::vendorForm($data);
		}
		$vendor = VendorQuery::create()->findOneByVendorid($data->vendorID);
		self::pw('page')->headline = "$vendor->name Purchase Orders";

		$filter = new Filters\Mpo\PurchaseOrder();
		$filter->filterInput(self::pw('input'));
		$filter->sortby(self::pw('page'));
		$orders = $filter->query->paginate(self::pw('input')->pageNum, 10);
		self::pw('page')->js .= self::pw('config')->twig->render('purchase-orders/list.js.twig');
		return self::listDisplay($data, $vendor, $orders);
	}

/* =============================================================
	Displays
============================================================= */
	private static function listDisplay($data, VendorModel $vendor, ModelPager $orders) {
		self::initHooks();
		return self::pw('config')->twig->render('purchase-orders/vendor/page.twig', ['configPo' => self::configPo(), 'vendor' => $vendor, 'orders' => $orders]);
	}

	private static function vendorForm($data) {
		return self::pw('config')->twig->render('purchase-orders/vendor/vendor-form.twig');
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMpo');

		$m->addHook('Page(pw_template=purchase-orders)::poUrl', function($event) {
			$event->return = self::poUrl($event->arguments(0));
		});

	}
}
