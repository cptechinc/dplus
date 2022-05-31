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
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Filters
use Dplus\Filters\Mso\SalesOrder as FilterSalesOrders;
// Mvc Controllers
use Controllers\Mso\SalesOrder\Base;
use Controllers\Mci\Ci\Ci;

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
		self::pw('page')->headline = "$data->custID Sales Orders";
		return self::listOrders($data);
	}

	private static function listOrders($data) {
		$filter = new FilterSalesOrders();
		$filter->user(self::pw('user'));
		$filter->filterInput(self::pw('input'));
		$filter->sortby(self::pw('page'));
		$orders = $filter->query->paginate(self::pw('input')->pageNum, 10);
		self::initHooks();
		return self::displayList($data, $orders);
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, ModelPager $orders) {
		$config = self::pw('config');
		$html = '';
		$html .= $config->twig->render('sales-orders/customer/search-form.twig', ['custID' => $data->custID, 'shiptoID' => $data->shiptoID]);
		$html .= $config->twig->render('sales-orders/customer/sales-order-list-links.twig', ['orders' => $orders]);
		$html .= '<div class="mb-3"></div>';
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager' => $orders]);
		return $html;
	}

	protected static function displayCustomerDenied($data) {
		return self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Access Denied', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "You don't have access to this Customer"]);
	}

/* =============================================================
	Url Functions
============================================================= */
	public static function listUrl($custID, $ordn = '') {
		if (empty($ordn)) {
			return self::_listUrl($custID);
		}
		$url = new Purl(self::_listUrl($custID));
		$filter = new FilterSalesOrders();

		if ($filter->exists($ordn)) {
			$url->query->set('focus', $ordn);
			$offset = $filter->positionQuick($ordn);
			$pagenbr = self::getPagenbrFromOffset($offset);
			$url = self::pw('modules')->get('Dpurl')->paginate($url, 'customer', $pagenbr);
		}
		return $url->getUrl();
	}

	public static function _listUrl($custID) {
		return self::orderListCustomerUrl($custID);
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMso');

		$m->addHook('Page(pw_template=sales-orders)::ciUrl', function($event) {
			$event->return = Ci::ciUrl($event->arguments(0));
		});
	}
}
