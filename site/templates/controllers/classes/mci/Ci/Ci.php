<?php namespace Controllers\Mci\Ci;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use CustomerQuery, Customer;
// Dpluso Model
use CustindexQuery, Custindex;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\CiLoadCustomerShipto;
// Dplus Validators
use Dplus\CodeValidators\Mar as MarValidator;
// Dplus Filters
use Dplus\Filters\Mar\Customer     as CustomerFilter;
use Dplus\Filters\Mso\SalesOrder   as SalesOrderFilter;
use Dplus\Filters\Mso\SalesHistory as SalesHistoryFilter;
use Dplus\Filters\Mqo\Quote        as QuoteFilter;
// Mvc Controllers
use Mvc\Controllers\AbstractController;
use Controllers\Mso\SalesOrder as ControllersSalesOrder;

class Ci extends Base {
	const SUBFUNCTIONS = [
		'pricing'        => [],
		'shiptos'        => ['title' => 'Ship-tos', 'path' => 'ship-tos'],
		'contacts'       => [],
		'salesorders'    => ['path' => 'sales-orders', 'title' => 'Sales Orders'],
		'saleshistory'   => ['path' => 'sales-history', 'title' => 'Sales History'],
		'customerpo'     => ['path' => 'cust-po', 'title' => 'Cust POs'],
		'quotes'         => [],
		'openinvoices'   => ['path' => 'open-invoices', 'title' => 'Open Invoices'],
		'payments'       => [],
		'credit'         => [],
		'standingorders' => ['path' => 'standing-orders', 'title' => 'Standing Orders'],
		'stock'          => [],
		'notes'          => [],
		'documents'      => [],
		'phonebook'      => [],
		'activity'       => [],
		'corebank'       => ['title' => 'Core'],
	];

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['custID|text', 'q|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');

		if (empty($data->custID) === false) {
			return self::customer($data);
		}
		return self::list($data);
	}

	public static function list($data) {
		$fields = ['q|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');
		$filter = new CustomerFilter();
		$filter->user(self::pw('user'));
		$filter->sortby($page);

		if ($data->q) {
			$data->q = strtoupper($data->q);

			if ($filter->exists($data->q)) {
				self::pw('session')->redirect(self::ciUrl($data->q), $http301 = false);
			}

			$filter->search($data->q);
			$page->headline = "CI: Searching for '$data->q'";
		}
		$customers = $filter->query->paginate(self::pw('input')->pageNum, 10);
		return self::displayList($data, $customers);
	}

	public static function customer($data) {
		$page   = self::pw('page');
		$config = self::pw('config');
		$fields = ['custID|text'];
		$data = self::sanitizeParametersShort($data, $fields);

		if (self::validateCustidPermission($data) === false) {
			self::displayInvalidCustomerOrPermissions($data);
		}

		$modules = self::pw('modules');
		$modules->get('DpagesMci')->init_customer_hooks();
		$modules->get('DpagesMci')->init_cipage();
		$customer = CustomerQuery::create()->findOneById($data->custID);
		$page->show_breadcrumbs = false;

		$page->headline = "CI: $customer->name";
		$config->scripts->append(self::getFileHasher()->getHashUrl('scripts/customer/ci-customer.js'));
		return self::displayCustomer($data, $customer);
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayList($data, PropelModelPager $customers) {
		$config = self::pw('config');

		$html = '';
		$html .= $config->twig->render('customers/customer-search.twig', ['customers' => $customers, 'datamatcher' => self::pw('modules')->get('RegexData'), 'q' => $data->q]);
		$html .= '<div class="mb-3"></div>';
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager' => $customers]);
		return $html;
	}

	private static function displayCustomer($data, Customer $customer) {
		$config = self::pw('config');
		$writer = self::pw('modules')->get('HtmlWriter');

		$html = '';
		$html .= $config->twig->render('customers/ci/bread-crumbs.twig', ['customer' => $customer]);
		$html .= $config->twig->render('customers/ci/customer/main.twig', ['customer' => $customer]);
		$html .= self::customerUserActions($customer);
		$html .= self::customerContacts($customer);
		$html .= self::customerSalesOrders($customer);
		$html .= self::customerSalesHistory($customer);
		$html .= self::customerQuotes($customer);
		return $html;
	}

	private static function customerUserActions(Customer $customer) {
		$modules = self::pw('modules');
		$config  = self::pw('config');
		$filter = $modules->get('FilterUserActions');
		$module_useractions = $modules->get('FilterUserActions');
		$query = $filter->get_actionsquery(self::pw('input'));
		$query->filterByStatusIncomplete();
		$query->filterByCustomerlink($customer->id);
		$actions = $query->paginate(1, 10);
		return $config->twig->render('customers/ci/customer/actions-panel.twig', ['module_useractions' => $module_useractions, 'actions' => $actions, 'resultscount'=> $actions->getNbResults()]);
	}

	private static function customerContacts(Customer $customer) {
		$config  = self::pw('config');
		$q = CustindexQuery::create();
		$q->filterByCustid($customer->id);
		$contacts = $q->paginate(1, 10);
		return $config->twig->render('customers/ci/customer/contacts-panel.twig', ['customer' => $customer, 'contacts' => $contacts, 'resultscount'=> $contacts->getNbResults()]);
	}

	private static function customerSalesOrders(Customer $customer) {
		$config  = self::pw('config');
		$page    = self::pw('page');
		$filter = new SalesOrderFilter();
		$filter->user(self::pw('user'));
		$filter->custid($customer->id);
		$filter->query->limit(10);
		$orders = $filter->query->paginate(1, 10);
		return $config->twig->render('customers/ci/customer/sales-orders-panel.twig', ['customer' => $customer, 'orders' => $orders, 'resultscount'=> $orders->getNbResults()]);
	}

	private static function customerSalesHistory(Customer $customer) {
		$config  = self::pw('config');
		$page    = self::pw('page');
		$filter = new SalesHistoryFilter();
		$filter->user(self::pw('user'));
		$filter->custid($customer->id);
		$filter->query->limit(10);
		$orders = $filter->query->paginate(1, 10);
		return $config->twig->render('customers/ci/customer/sales-history-panel.twig', ['customer' => $customer, 'orders' => $orders, 'resultscount'=> $orders->getNbResults(), 'orderpage' => self::pw('pages')->get('pw_template=sales-order-view')->url, 'shipped_orders_list' => $page->cust_saleshistoryURL($customer->id)]);
	}

	private static function customerQuotes(Customer $customer) {
		if (self::pw('user')->has_function('mqo')  === false) {
			return '';
		}
		$config  = self::pw('config');
		$page    = self::pw('page');
		$filter = new QuoteFilter();
		$filter->user(self::pw('user'));
		$filter->custid($customer->id);
		$filter->query->limit(10);
		$quotes = $filter->query->paginate(1, 10);
		return $config->twig->render('customers/ci/customer/quotes-panel.twig', ['customer' => $customer, 'quotes' => $quotes, 'resultscount'=> $quotes->getNbResults(), 'quotepage' => self::pw('pages')->get('pw_template=quote-view')->url, 'quotes_list' => $page->cust_quotesURL($customer->id)]);
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMii');

		$m->addHook('Page(pw_template=ci)::ciPermittedSubfunctions', function($event) {
			$user = self::pw('user');
			$allowed = [];
			$iio = self::getCio();
			foreach (self::SUBFUNCTIONS as $option => $data) {
				if ($iio->allowUser($user, $option)) {
					$allowed[$option] = $data;
				}
			}
			$event->return = $allowed;
		});

		$m->addHook('Page(pw_template=ci)::ciSubfunctionUrl', function($event) {
			$custID = $event->arguments(0);
			$key    = $event->arguments(1);
			$path   = $key;

			if (array_key_exists($key, self::SUBFUNCTIONS)) {
				if (array_key_exists('path', self::SUBFUNCTIONS[$key])) {
					$path = self::SUBFUNCTIONS[$key]['path'];
				}
			}

			$event->return = self::ciSubfunctionUrl($custID, $path);
		});

		$m->addHook('Page(pw_template=ci)::salesorderUrl', function($event) {
			$event->return = ControllersSalesOrder\SalesOrder::orderUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=ci)::salesorderListUrl', function($event) {
			$event->return = ControllersSalesOrder\SalesOrder::orderListCustomerUrl($event->arguments(0));
		});
	}
}
