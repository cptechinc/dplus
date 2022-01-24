<?php namespace Controllers\Mvi\Vi;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use VendorQuery, Vendor;
// ProcessWire Classes, Modules
use ProcessWire\Page;
use ProcessWire\WireData;
// Dplus Validators
use Dplus\CodeValidators\Map as MapValidator;
// Dplus Filters
use Dplus\Filters;
use Dplus\Filters\Map\Vendor     as VendorFilter;
// Dplus Configs
use Dplus\Configs;
// Mvc Controllers
use Mvc\Controllers\Controller;
use Controllers\Mpo\PurchaseOrder as ControllersPo;
use Controllers\Mpo\ApInvoice\Lists\ApInvoice as ControllerApInvoice;
use Controllers\Mpo\PurchaseOrder\Epo\Create as ControllerPoCreate;

class Vi extends Base {
	const SUBFUNCTIONS = [
		'ship-froms'       => ['title' => 'Ship-Froms'],
		'contacts'         => [],
		'purchase-orders'  => ['path' => 'purchase-orders', 'title' => 'Purchase Orders'],
		'unreleased '      => ['path' => 'purchase-orders/unreleased', 'title' => 'Unreleased Purchase Orders'],
		'purchase-history' => ['path' => 'purchase-history', 'title' => 'Purchase History'],
		'openinvoices'     => ['path' => 'open-invoices', 'title' => 'Open Invoices'],
		'univoiced'        => ['path' => 'purchase-orders/uninvoiced', 'title' => 'Uninvoiced POs'],
		'payments'         => ['title' => 'Payments'],
		'summary'          => ['title' => '24-Month Summary', 'path' => 'summary'],
		'costing'          => ['title' => 'Costing'],
		'notes'            => [],
		'documents'        => [],
	];

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['vendorID|text', 'q|text'];
		self::sanitizeParametersShort($data, $fields);

		if (empty($data->vendorID) === false) {
			return self::vendor($data);
		}
		return self::list($data);
	}

	private static function list($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		$filter = new VendorFilter();
		$filter->sortby(self::pw('page'));

		if ($data->q) {
			$data->q = strtoupper($data->q);

			if ($filter->exists($data->q)) {
				self::pw('session')->redirect(self::viUrl($data->q), $http301 = false);
			}

			$filter->search($data->q);
			self::pw('page')->headline = "VI: Searching for '$data->q'";
		}
		$vendors = $filter->query->paginate(self::pw('input')->pageNum, 10);
		return self::displayList($data, $vendors);
	}

	private static function vendor($data) {
		$fields = ['vendorID|text'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateVendoridPermission($data) === false) {
			return self::displayInvalidVendorOrPermissions($data);
		}

		$vendor = self::getVendor($data->vendorID);
		$page   = self::pw('page');
		$page->show_breadcrumbs = false;

		$page->headline = "VI: $vendor->name";
		self::pw('config')->po = Configs\Po::config();
		return self::displayVendor($data, $vendor, self::getApData($vendor));
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $vendors) {
		$config = self::pw('config');

		$html = '';
		$html .= $config->twig->render('vendors/vendor-search.twig', ['vendors' => $vendors, 'datamatcher' => self::pw('modules')->get('RegexData'), 'q' => $data->q]);
		$html .= '<div class="mb-3"></div>';
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager' => $vendors]);
		return $html;
	}

	private static function displayVendor($data, Vendor $vendor, WireData $apData) {
		$config = self::pw('config');

		$html = '';
		$html .= self::displayBreadCrumbs($data);
		$html .= $config->twig->render('vendors/vi/vendor/display.twig', ['vendor' => $vendor, 'data' => $apData]);
		return $html;
	}

	private static function getApData(Vendor $vendor) {
		$data = new WireData();

		$filter = new Filters\Mpo\PurchaseOrder();
		$filter->vendorid($vendor->id);
		$filter->query->limit(10);
		$data->orders = $filter->query->paginate(1, 10);

		$filter = new Filters\Mpo\ApInvoice();
		$filter->vendorid($vendor->id);
		$filter->query->limit(10);
		$data->invoices = $filter->query->paginate(1, 10);
		return $data;
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMvi');

		$m->addHook('Page(pw_template=vi)::viUrl', function($event) {
			$event->return = self::viUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=vi)::viShipfromUrl', function($event) {
			$event->return = self::viShipfromUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=vi)::viPurchaseHistoryUrl', function($event) {
			$event->return = self::viPurchaseHistoryUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=vi)::viPermittedSubfunctions', function($event) {
			$user = self::pw('user');
			$allowed = [];
			$vio = self::getVio();
			foreach (self::SUBFUNCTIONS as $option => $data) {
				if ($vio->allowUser($user, $option)) {
					$allowed[$option] = $data;
				}
			}
			$event->return = $allowed;
		});

		$m->addHook('Page(pw_template=vi)::viSubfunctionUrl', function($event) {
			$vendorID = $event->arguments(0);
			$key    = $event->arguments(1);
			$path   = $key;

			if (array_key_exists($key, self::SUBFUNCTIONS)) {
				if (array_key_exists('path', self::SUBFUNCTIONS[$key])) {
					$path = self::SUBFUNCTIONS[$key]['path'];
				}
			}

			$event->return = self::viSubfunctionUrl($vendorID, $path);
		});

		$m->addHook('Page(pw_template=vi)::poUrl', function($event) {
			$event->return = ControllersPo\PurchaseOrder::poUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=vi)::poListUrl', function($event) {
			$event->return = ControllersPo\PurchaseOrder::poListVendorUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=vi)::apInvoiceUrl', function($event) {
			$event->return = ControllerApInvoice::invoiceUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=vi)::apInvoiceListUrl', function($event) {
			$event->return = ControllerApInvoice::listVendorUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=vi)::createPoUrl', function($event) {
			$event->return = ControllerPoCreate::createVendorPoUrl($event->arguments(0));
		});
	}
}
