<?php namespace Controllers\Mpo\ApInvoice\Lists;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager as ModelPager;
// Dplus Model
use PurchaseOrderQuery, PurchaseOrder as PoModel;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\WireData;
// Dplus Configs
use Dplus\Configs;
// Dplus Filters
use Dplus\Filters;
// Mvc Controllers
use Mvc\Controllers\Controller;
use Controllers\Mpo\ApInvoice\Base;

class ApInvoice extends Base {
/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = [];
		self::sanitizeParametersShort($data, $fields);
		return self::list($data);
	}

	public static function list($data) {
		$filter = new Filters\Mpo\ApInvoice();
		$filter->filterInput(self::pw('input'));
		$filter->sortby(self::pw('page'));
		$invoices = $filter->query->paginate(self::pw('input')->pageNum, 10);
		// self::pw('page')->js .= self::pw('config')->twig->render('purchase-orders/list.js.twig', []);
		return self::listDisplay($data, $invoices);
	}

/* =============================================================
	Displays
============================================================= */
	private static function listDisplay($data, ModelPager $invoices) {
		self::initHooks();
		return self::pw('config')->twig->render('purchase-orders/invoices/display.twig', ['configPo' => Configs\Po::config(), 'invoices' => $invoices]);
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMpo');

		$m->addHook('Page(pw_template=purchase-orders-invoices)::apInvoiceUrl', function($event) {
			$event->return = self::apInvoiceUrl($event->arguments(0));
		});
	}
}
