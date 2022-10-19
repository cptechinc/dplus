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
use Mvc\Controllers\Controller;
use Controllers\Mso\SalesOrder as ControllersSalesOrder;
use Controllers\Mqo\Quote      as ControllersQuote;
use Controllers\Misc\Cart\Cart;

class Ci extends Base {
	const SUBFUNCTIONS = [
		'pricing'        => [],
		'shiptos'        => ['title' => 'Ship-tos', 'path' => 'ship-tos'],
		'contacts'       => [],
		'salesorders'    => ['path' => 'sales-orders', 'title' => 'Sales Orders'],
		'saleshistory'   => ['path' => 'sales-history', 'title' => 'Sales History'],
		'customerpo'     => ['path' => 'purchase-orders', 'title' => 'Cust POs'],
		'quotes'         => [],
		'openinvoices'   => ['path' => 'open-invoices', 'title' => 'Open Invoices'],
		'payments'       => [],
		'credit'         => [],
		'standingorders' => ['path' => 'standing-orders', 'title' => 'Standing Orders'],
		// 'stock'          => [],
		// 'notes'          => [],
		'documents'      => [],
		'phonebook'      => [],
		// 'activity'       => [],
		'corebank'       => ['title' => 'Core'],
	];

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['custID|string', 'q|text'];
		self::sanitizeParametersShort($data, $fields);

		if (empty($data->custID) === false) {
			return self::customer($data);
		}
		return self::list($data);
	}

	private static function list($data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);
		$filter = new CustomerFilter();
		$filter->user(self::pw('user'));
		$filter->sortby(self::pw('page'));

		if ($data->q) {
			$data->q = strtoupper($data->q);

			if ($filter->exists($data->q)) {
				self::pw('session')->redirect(self::ciUrl($data->q), $http301 = false);
			}

			$filter->search($data->q);
			self::pw('page')->headline = "CI: Searching for '$data->q'";
		}
		$customers = $filter->query->paginate(self::pw('input')->pageNum, 10);
		return self::displayList($data, $customers);
	}

	private static function customer($data) {
		$fields = ['custID|string'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateCustidPermission($data) === false) {
			return self::displayInvalidCustomerOrPermissions($data);
		}
		
		$customer = CustomerQuery::create()->findOneById($data->custID);
		$page   = self::pw('page');
		$page->show_breadcrumbs = false;

		$page->headline = "CI: $customer->name";
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/customer/ci-customer.js'));
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

		$html = '';
		$html .= self::displayBreadCrumbs($data);
		$html .= $config->twig->render('customers/ci/customer/main.twig', ['customer' => $customer]);
		$html .= self::displayUserActions($customer);
		$html .= self::displayContacts($customer);
		$html .= self::displaySalesOrders($customer);
		$html .= self::displayInvoices($customer);
		$html .= self::displayQuotes($customer);
		return $html;
	}

	private static function displayUserActions(Customer $customer) {
		$filter = self::pw('modules')->get('FilterUserActions');
		$query = $filter->get_actionsquery(self::pw('input'));
		$query->filterByStatusIncomplete();
		$query->filterByCustomerlink($customer->id);
		$actions = $query->paginate(1, 10);
		return self::pw('config')->twig->render('customers/ci/customer/panels/actions.twig', ['module_useractions' => $filter, 'actions' => $actions]);
	}

	private static function displayContacts(Customer $customer) {
		$config  = self::pw('config');
		$q = CustindexQuery::create();
		$q->filterByCustid($customer->id);
		$contacts = $q->paginate(1, 10);
		return $config->twig->render('customers/ci/customer/panels/contacts.twig', ['contacts' => $contacts]);
	}

	private static function displaySalesOrders(Customer $customer) {
		$config  = self::pw('config');
		$filter = new SalesOrderFilter();
		$filter->user(self::pw('user'));
		$filter->custid($customer->id);
		$filter->query->limit(10);
		$orders = $filter->query->paginate(1, 10);
		return $config->twig->render('customers/ci/customer/panels/sales-orders.twig', ['orders' => $orders]);
	}

	private static function displayInvoices(Customer $customer) {
		$config  = self::pw('config');
		$filter = new SalesHistoryFilter();
		$filter->user(self::pw('user'));
		$filter->custid($customer->id);
		$filter->query->limit(10);
		$orders = $filter->query->paginate(1, 10);
		return $config->twig->render('customers/ci/customer/panels/invoices.twig', ['orders' => $orders]);
	}

	private static function displayQuotes(Customer $customer) {
		if (self::pw('user')->has_function('mqo')  === false) {
			return '';
		}
		$config  = self::pw('config');
		$filter = new QuoteFilter();
		$filter->user(self::pw('user'));
		$filter->custid($customer->id);
		$filter->query->limit(10);
		$quotes = $filter->query->paginate(1, 10);
		return $config->twig->render('customers/ci/customer/panels/quotes.twig', ['quotes' => $quotes]);
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMci');

		$m->addHook('Page(pw_template=ci)::ciUrl', function($event) {
			$event->return = self::ciUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=ci)::ciShiptoUrl', function($event) {
			$event->return = self::ciShiptoUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=ci)::ciContactsUrl', function($event) {
			$event->return = self::ciContactsUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=ci)::ciContactUrl', function($event) {
			$event->return = self::ciContactUrl($event->arguments(0), $event->arguments(1), $event->arguments(2));
		});

		$m->addHook('Page(pw_template=ci)::ciContactEditUrl', function($event) {
			$event->return = self::ciContactEditUrl($event->arguments(0), $event->arguments(1), $event->arguments(2));
		});

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

		$m->addHook('Page(pw_template=ci)::orderUrl', function($event) {
			$event->return = ControllersSalesOrder\SalesOrder::orderUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=ci)::orderListUrl', function($event) {
			$event->return = ControllersSalesOrder\SalesOrder::orderListCustomerUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=ci)::invoiceListUrl', function($event) {
			$event->return = ControllersSalesOrder\Lists\Invoices\Customer::listUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=ci)::cartCustomerUrl', function($event) {
			$event->return = Cart::setCustomerUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=ci)::quoteUrl', function($event) {
			$event->return = ControllersQuote\Quote::quoteUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=ci)::quoteListUrl', function($event) {
			$event->return = ControllersQuote\Lists\Customer::listUrl($event->arguments(0), $event->arguments(1));
		});
	}
}
