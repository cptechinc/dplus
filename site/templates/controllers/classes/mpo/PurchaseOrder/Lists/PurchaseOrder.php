<?php namespace Controllers\Mpo\PurchaseOrder\Lists;
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
use Controllers\Mpo\PurchaseOrder\Base;

class PurchaseOrder extends Base {
/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = [];
		self::sanitizeParametersShort($data, $fields);
		return self::list($data);
	}

	public static function list($data) {
		$filter = new Filters\Mpo\PurchaseOrder();
		$filter->filterInput(self::pw('input'));
		$filter->sortby(self::pw('page'));
		$orders = $filter->query->paginate(self::pw('input')->pageNum, 10);
		self::pw('page')->js .= self::pw('config')->twig->render('purchase-orders/list.js.twig', []);
		return self::listDisplay($data, $orders);
	}

/* =============================================================
	Displays
============================================================= */
	private static function listDisplay($data, ModelPager $orders) {
		self::initHooks();
		return self::pw('config')->twig->render('purchase-orders/page.twig', ['configPo' => self::configPo(), 'orders' => $orders]);
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMpo');

		$m->addHook('Page(pw_template=purchase-orders)::poUrl', function($event) {
			$event->return = self::poUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=purchase-orders)::poListUrl', function($event) {
			$event->return = self::poListUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=purchase-orders)::poEditUrl', function($event) {
			$event->return = self::poEditUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=purchase-orders)::poCreateUrl', function($event) {
			$event->return = self::poCreateUrl($event->arguments(0));
		});
	}
}
