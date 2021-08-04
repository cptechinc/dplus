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
use Mvc\Controllers\AbstractController;
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
		$configPo = Configs\Po::config();
		return self::pw('config')->twig->render('purchase-orders/page.twig', ['configPo' => $configPo, 'orders' => $orders]);
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
