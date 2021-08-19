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
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Mvc Controllers
use Controllers\Mso\SalesOrder\Base;

class Customer extends Base {

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['custID|text', 'shiptoID|text'];
		self::sanitizeParametersShort($data, $fields);
		$validate = new Validators\Mar();
		if ($validate->custid($data->custID)) {
			if (self::pw('user')->has_customer($data->custID) === false) {
				return self::displayCustomerDenied($data);
			}
		}
		self::pw('page')->headline = "$data->custID Invoices";
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
		$html .= $config->twig->render('sales-orders/sales-history/customer/search-form.twig');
		$html .= $config->twig->render('sales-orders/sales-history/customer/sales-history-list-links.twig', ['orders' => $orders]);
		$html .= '<div class="mb-3"></div>';
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager' => $orders]);
		return $html;
	}

/* =============================================================
	Url Functions
============================================================= */
	public static function listUrl($custID, $ordn = '') {
		if (empty($ordn)) {
			return self::_listUrl($custID);
		}
		$url = new Purl(self::_listUrl($custID));
		$filter = new FilterInvoices();

		if ($filter->exists($ordn)) {
			$url->query->set('focus', $ordn);
			$offset = $filter->positionQuick($ordn);
			$pagenbr = self::getPagenbrFromOffset($offset);
			$url = self::pw('modules')->get('Dpurl')->paginate($url, 'customer', $pagenbr);
		}
		return $url->getUrl();
	}

	public static function _listUrl($custID) {
		$url = new Purl(self::pw('pages')->get('pw_template=sales-orders-invoices')->url);
		$url->path->add('customer');
		$url->query->set('custID', $custID);
		return $url->getUrl();
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
