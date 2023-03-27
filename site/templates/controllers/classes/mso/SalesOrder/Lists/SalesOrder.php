<?php namespace Controllers\Mso\SalesOrder\Lists;

use stdClass;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager as ModelPager;
// Dplus Model
use SalesOrderQuery, SalesOrder as SoModel;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module;
// Dplus Filters
use Dplus\Filters\Mso\SalesOrder as FilterSalesOrders;
// Mvc Controllers
use Controllers\Mso\SalesOrder\Base;

class SalesOrder extends Base {

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		if (static::validateUserPermission() === false) {
			return static::renderUserNotPermittedAlert();
		}

		return self::listOrders($data);
	}

	public static function listOrders($data) {
		$filter = new FilterSalesOrders();
		$filter->user(self::pw('user'));
		$filter->filterInput(self::pw('input'));
		$filter->sortby(self::pw('page'));
		$orders = $filter->query->paginate(self::pw('input')->pageNum, 10);
		return self::displayList($orders);
	}

/* =============================================================
	Displays
============================================================= */
	public static function displayList(ModelPager $orders) {
		$config = self::pw('config');
		$html = '';
		$html .= $config->twig->render('sales-orders/search-form.twig', ['input' => self::pw('input')]);
		$html .= $config->twig->render('sales-orders/sales-orders-list-links.twig', ['orders' => $orders, 'orderpage' => self::pw('pages')->get('pw_template=sales-order-view')->url]);
		$html .= '<div class="mb-3"></div>';
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager' => $orders]);
		return $html;
	}

/* =============================================================
	Url Functions
============================================================= */
	public static function listUrl($ordn = '') {
		if (empty($ordn)) {
			return self::_listUrl();
		}
		$url = new Purl(self::pw('pages')->get('pw_template=sales-orders')->url);
		$filter = new FilterSalesOrders();

		if ($filter->exists($ordn)) {
			$url->query->set('focus', $ordn);
			$offset = $filter->positionQuick($ordn);
			$pagenbr = self::getPagenbrFromOffset($offset);
			$url = self::pw('modules')->get('Dpurl')->paginate($url, self::pw('pages')->get('pw_template=sales-orders')->name, $pagenbr);
		}
		return $url->getUrl();
	}

	public static function _listUrl() {
		return self::pw('pages')->get('pw_template=sales-orders')->url;
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function initHooks() { // TODO HOOKS for CI
		$m = self::pw('modules')->get('DpagesMso');

		$m->addHook('Page(pw_template=sales-orders)::orderUrl', function($event) {
			$event->return = self::orderUrl($event->arguments(0));
		});
	}
}
