<?php namespace Controllers\Mci\Ci;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use CustomerQuery, Customer;
use CustomerShiptoQuery, CustomerShipto;
// Dpluso Model
use CustindexQuery, Custindex;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\CiLoadCustomerShipto;
// Dplus Validators
use Dplus\CodeValidators\Mar as MarValidator;
// Dplus Filters
use Dplus\Filters;
use Dplus\Filters\Mar\Customer     as CustomerFilter;
use Dplus\Filters\Mso\SalesOrder   as SalesOrderFilter;
use Dplus\Filters\Mso\SalesHistory as SalesHistoryFilter;
use Dplus\Filters\Mqo\Quote        as QuoteFilter;
// Mvc Controllers
use Mvc\Controllers\Controller;
use Controllers\Mso\SalesOrder as ControllersSalesOrder;

class Shipto extends Base {

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['custID|text', 'shiptoID|text', 'q|text'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateCustidPermission($data) === false) {
			return self::displayInvalidCustomerOrPermissions($data);
		}

		if (empty($data->shiptoID) === false) {
			return self::shipto($data);
		}
		return self::list($data);
	}

	private static function list($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		$filter = new Filters\Mar\Shipto();
		$filter->custid($data->custID);
		$filter->user(self::pw('user'));
		$filter->sortby(self::pw('page'));
		$shiptos = $filter->query->paginate(self::pw('input')->pageNum, 0);
		$customer = self::getCustomer($data->custID);
		self::pw('page')->headline = "CI: $customer->name Ship-tos";
		return self::displayList($data, $customer, $shiptos);
	}

	private static function shipto($data) {
		$fields = ['custID|text'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateShiptoAccess($data) === false) {
			return self::displayInvalidShiptoOrPermissions($data);
		}
		$shipto = self::getShipto($data->custID, $data->shiptoID);
		self::pw('page')->headline = "CI: {$shipto->customer->name} Ship-to $shipto->shiptoid";
		return self::displayShipto($data, $shipto);
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, Customer $customer, PropelModelPager $shiptos) {
		$config = self::pw('config');

		$html = '';
		$html .= self::displayBreadCrumbs($data);
		$html .= $config->twig->render('customers/ci/ship-tos/list.twig', ['customer' => $customer, 'shiptos' => $shiptos]);
		return $html;
	}

	private static function displayShipto($data, CustomerShipto $shipto) {
		$config = self::pw('config');

		$html = '';
		$html .= self::displayBreadCrumbs($data);
		$html .= $config->twig->render('customers/ci/ship-tos/ship-to/main.twig', ['shipto' => $shipto]);
		$html .= self::displayUserActions($shipto);
		$html .= self::displayContacts($shipto);
		$html .= self::displaySalesOrders($shipto);
		$html .= self::displayInvoices($shipto);
		$html .= self::displayQuotes($shipto);
		return $html;
	}

	private static function displayUserActions(CustomerShipto $shipto) {
		$filter = self::pw('modules')->get('FilterUserActions');
		$query = $filter->get_actionsquery(self::pw('input'));
		$query->filterByStatusIncomplete();
		$query->filterByCustomerlink($shipto->custid);
		$query->filterByShiptolink($shipto->shiptoid);
		$actions = $query->paginate(1, 10);
		return self::pw('config')->twig->render('customers/ci/customer/panels/actions.twig', ['module_useractions' => $filter, 'actions' => $actions]);
	}

	private static function displayContacts(CustomerShipto $shipto) {
		$config  = self::pw('config');
		$q = CustindexQuery::create();
		$q->filterByCustid($shipto->custid);
		$q->filterByShiptoid($shipto->shiptoid);
		$contacts = $q->paginate(1, 10);
		return $config->twig->render('customers/ci/customer/panels/contacts.twig', ['contacts' => $contacts]);
	}

	private static function displaySalesOrders(CustomerShipto $shipto) {
		$config  = self::pw('config');
		$filter = new SalesOrderFilter();
		$filter->user(self::pw('user'));
		$filter->custid($shipto->custid);
		$filter->shiptoid($shipto->shiptoid);
		$filter->query->limit(10);
		$orders = $filter->query->paginate(1, 10);
		return $config->twig->render('customers/ci/customer/panels/sales-orders.twig', ['orders' => $orders]);
	}

	private static function displayInvoices(CustomerShipto $shipto) {
		$config  = self::pw('config');
		$filter = new SalesHistoryFilter();
		$filter->user(self::pw('user'));
		$filter->custid($shipto->custid);
		$filter->shiptoid($shipto->shiptoid);
		$filter->query->limit(10);
		$orders = $filter->query->paginate(1, 10);
		return $config->twig->render('customers/ci/customer/panels/invoices.twig', ['orders' => $orders]);
	}

	private static function displayQuotes(CustomerShipto $shipto) {
		if (self::pw('user')->has_function('mqo')  === false) {
			return '';
		}
		$config  = self::pw('config');
		$filter = new QuoteFilter();
		$filter->user(self::pw('user'));
		$filter->custid($shipto->custid);
		$filter->shiptoid($shipto->shiptoid);
		$filter->query->limit(10);
		$quotes = $filter->query->paginate(1, 10);
		return $config->twig->render('customers/ci/customer/panels/quotes.twig', ['quotes' => $quotes]);
	}

	// NOTE: KEEP public to be used in Ci\Contacts
	public static function displayInvalidShiptoOrPermissions($data) {
		if (self::getValidator()->custShiptoid($data->custID, $data->shiptoID) === false) {
			return self::displayInvalidShiptoid($data);
		}
		if (self::pw('user')->hasCustomer($data->custID, $data->shiptoID) === false) {
			return displayUserNotAllowedShipto($data);
		}
		return '';
	}

	protected static function displayInvalidShiptoid($data) {
		return self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Ship-to $data->shiptoID not found"]);
	}

	protected static function displayUserNotAllowedShipto($data) {
		return self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Access Denied', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "You do not have permission to access to $data->shiptoID"]);
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function validateShiptoAccess($data) {
		if (self::getValidator()->custShiptoid($data->custID, $data->shiptoID) === false) {
			return false;
		}
		return self::pw('user')->hasCustomer($data->custID, $data->shiptoID);
	}

	public static function getShipto($custID, $shiptoID) {
		return CustomerShiptoQuery::create()->filterByCustid($custID)->filterByShiptoid($shiptoID)->findOne();
	}
}
