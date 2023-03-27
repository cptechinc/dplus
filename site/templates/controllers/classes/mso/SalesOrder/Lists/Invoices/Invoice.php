<?php namespace Controllers\Mso\SalesOrder\Lists\Invoices;

use stdClass;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager as ModelPager;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module;
// Dplus Filters
use Dplus\Filters\Mso\SalesHistory as FilterInvoices;
// Mvc Controllers
use Controllers\Mso\SalesOrder\Base;

class Invoice extends Base {

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		if (static::validateUserPermission() === false) {
			return static::renderUserNotPermittedAlert();
		}
		return self::listInvoices($data);
	}

	private static function listInvoices($data) {
		$filter = new FilterInvoices();
		$filter->user(self::pw('user'));
		$filter->filterInput(self::pw('input'));
		$filter->sortby(self::pw('page'));
		if (self::pw('input')->get->offsetExists('orderby') === false) {
			$filter->query->orderByDate_ordered('DESC');
		}
		$orders = $filter->query->paginate(self::pw('input')->pageNum, 10);
		return self::displayList($orders);
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList(ModelPager $orders) {
		$config = self::pw('config');

		$html = '';
		$html .= $config->twig->render('sales-orders/sales-history/search-form.twig');
		$html .= $config->twig->render('sales-orders/sales-history/sales-history-list-links.twig', ['orders' => $orders]);
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
		$url = new Purl(self::_listUrl());
		$filter = new FilterInvoices();

		if ($filter->exists($ordn)) {
			$url->query->set('focus', $ordn);
			$offset = $filter->positionQuick($ordn);
			$pagenbr = self::getPagenbrFromOffset($offset);
			$url = self::pw('modules')->get('Dpurl')->paginate($url, self::pw('pages')->get('pw_template=sales-orders-invoices')->name, $pagenbr);
		}
		return $url->getUrl();
	}

	public static function _listUrl() {
		return self::pw('pages')->get('pw_template=sales-orders-invoices')->url;
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMso');

		$m->addHook('Page(pw_template=sales-orders-invoices)::orderUrl', function($event) {
			$event->return = self::orderUrl($event->arguments(0));
		});
	}
}
