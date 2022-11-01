<?php namespace Controllers\Mci\Ci;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Models
use Customer;
// Dpluso Models
use CustindexQuery;
// ProcessWire Classes, Modules
use ProcessWire\WireData;
use ProcessWire\Wire404Exception;
// Dplus Filters
use Dplus\Filters;
// Dplus Mar
use Dplus\Mar\Armain\Cmm;
// Mvc Controllers
use Controllers\Mso\SalesOrder as SalesOrderControllers;
use Controllers\Mqo\Quote      as QuoteControllers;
use Controllers\Misc\Cart\Cart;

/**
 * Ci
 * Handles CI Page
 */
class Ci extends AbstractController {
	const TITLE      = 'Customer Information';
	const SUMMARY    = 'View Customer Information';
	const SHOWONPAGE = 10;

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
	1. Indexes
============================================================= */
	public static function index(WireData $data) {
		if (self::validateUserPermission() === false) {
			throw new Wire404Exception();
		}
		$fields = ['custID|string', 'q|text', 'rid|int'];
		self::sanitizeParametersShort($data, $fields);

		if (empty($data->rid) === false) {
			return self::customer($data);
		}
		return self::list($data);
	}

	/**
	 * List Customers
	 * @param  WireData $data
	 * @return string
	 */
	private static function list(WireData $data) {
		$fields = ['q|text'];
		self::sanitizeParametersShort($data, $fields);

		if ($data->q) {
			$rID = Cmm::instance()->ridByCustid($data->q);
			if ($rID) {
				self::pw('session')->redirect(self::custUrl($rID), $http301 = false);
			}
			self::pw('page')->headline = "CI: Searching for '$data->q'";
		}
		$customers = self::getCustomerList($data);
		return self::displayList($data, $customers);
	}

	/**
	 * Customer Page
	 * @param  WireData $data
	 * @return string
	 */
	private static function customer(WireData $data) {
		if (self::validateCustomerByRid($data->rid) === false) {
			self::pw('session')->redirect(self::url(), $http301=false);
		}

		$cmm = Cmm::instance();
		if (self::validateUserHasCustomerPermission(null, $cmm->custidByRid($data->rid)) === false) {
			throw new Wire404Exception();
		}
		$customer = $cmm->customerByRid($data->rid);
		$customer->salesOrders  = self::getCustomerSalesOrders($customer->id);
		$customer->salesHistory = self::getCustomerSalesHistory($customer->id);
		$customer->quotes       = self::getCustomerQuotes($customer->id);
		$customer->contacts     = self::getCustomerContacts($customer->id);
		self::pw('page')->headline = "CI: $customer->name";
		self::pw('page')->custid   = $customer->id;
		return self::displayCustomer($data, $customer);
	}

/* =============================================================
	2. Validations
============================================================= */

/* =============================================================
	3. Data Requests / Data Fetching
============================================================= */
	/**
	 * Return Customer related Sales Orders
	 * @param  string $custID     Customer ID
	 * @param  int    $limit      Number of Results to return
	 * @return PropelModelPager   [SalesOrder]
	 */
	private static function getCustomerSalesOrders($custID, $limit = 10) {
		$filter = new Filters\Mso\SalesOrder();
		$filter->user(self::pw('user'));
		$filter->custid($custID);
		return $filter->query->paginate(1, $limit);
	}

	/**
	 * Return Customer related Sales History
	 * @param  string $custID     Customer ID
	 * @param  int    $limit      Number of Results to return
	 * @return PropelModelPager   [Saleshistory]
	 */
	private static function getCustomerSalesHistory($custID, $limit = 10) {
		$filter = new Filters\Mso\SalesHistory();
		$filter->user(self::pw('user'));
		$filter->custid($custID);
		return $filter->query->paginate(1, $limit);
	}

	/**
	 * Return Customer related Quotes
	 * @param  string $custID     Customer ID
	 * @param  int    $limit      Number of Results to return
	 * @return PropelModelPager   [Quote]
	 */
	private static function getCustomerQuotes($custID, $limit = 10) {
		$filter = new Filters\Mqo\Quote();
		$filter->user(self::pw('user'));
		$filter->custid($custID);
		return $filter->query->paginate(1, $limit);
	}

	/**
	 * Return Customer related Contacts
	 * @param  string $custID     Customer ID
	 * @param  int    $limit      Number of Results to return
	 * @return PropelModelPager   [Custindex]
	 */
	private static function getCustomerContacts($custID, $limit = 10) {
		$q = CustindexQuery::create();
		$q->filterByCustid($custID);
		return $q->paginate(1, 10);
	}

	/**
	 * Return List of Customers
	 * @param  WireData $data
	 * @return PropelModelPager  [Customer]
	 */
	private static function getCustomerList(WireData $data) {
		$filter = new Filters\Mar\Customer();
		$filter->user(self::pw('user'));
		$filter->sortby(self::pw('page'));
		if ($data->q) {
			$filter->search($data->q);
		}
		return $filter->query->paginate(self::pw('input')->pageNum, self::SHOWONPAGE);
	}

/* =============================================================
	4. URLs
============================================================= */

/* =============================================================
	5. Displays
============================================================= */
	/**
	 * Return Customer List Display
	 * @param  WireData         $data
	 * @param  PropelModelPager $customers
	 * @return string
	 */
	private static function displayList(WireData $data, PropelModelPager $customers) {
		$html = '';
		$html .= self::renderList($data, $customers);
		return $html;
	}

	/**
	 * Return Customer Page Display
	 * @param  WireData $data
	 * @param  Customer $customer
	 * @return string
	 */
	private static function displayCustomer(WireData $data, Customer $customer) {
		$html = '';
		$html .= self::renderCustomer($data, $customer);
		return $html;
	}

/* =============================================================
	6. HTML Rendering
============================================================= */
	/**
	 * Render Customer List Page HTML
	 * @param WireData         $data
	 * @param PropelModelPager $customers
	 * @return string
	 */
	private static function renderList(WireData $data, PropelModelPager $customers) {
		return self::pw('config')->twig->render('customers/ci/.new/list/display.twig', ['customers' => $customers, 'datamatcher' => self::pw('modules')->get('RegexData')]);
	}

	/**
	 * Render Customer Page HTML
	 * @param  WireData $data
	 * @param  Customer $customer
	 * @return string
	 */
	private static function renderCustomer(WireData $data, Customer $customer) {
		return self::pw('config')->twig->render('customers/ci/.new/customer/display.twig', ['customer' => $customer]);
	}

/* =============================================================
	7. Class / Module Getting
============================================================= */

/* =============================================================
	8. Supplemental
============================================================= */

/* =============================================================
	9. Hooks / Object Decorating
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMci');

		$m->addHookProperty('Page(pw_template=ci)::custid', function($event) {
			$page = $event->object;
			if ($page->aCustid) {
				$event->return = $page->aCustid;
				return true;
			}
			$page->aCustid = Cmm::instance()->custidByRid($page->wire('input')->get->int('rid'));
			$event->return = $page->aCustid;
		});

		$m->addHook('Page(pw_template=ci)::custUrl', function($event) {
			$event->return = self::custUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=ci)::ciUrl', function($event) {
			$event->return = self::custUrl($event->arguments(0));
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
			$event->return = SalesOrderControllers\SalesOrder::orderUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=ci)::orderListUrl', function($event) {
			$event->return = SalesOrderControllers\SalesOrder::orderListCustomerUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=ci)::invoiceListUrl', function($event) {
			$event->return = SalesOrderControllers\Lists\Invoices\Customer::listUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=ci)::cartCustomerUrl', function($event) {
			$event->return = Cart::setCustomerUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=ci)::quoteUrl', function($event) {
			$event->return = QuoteControllers\Quote::quoteUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=ci)::quoteListUrl', function($event) {
			$event->return = QuoteControllers\Lists\Customer::listUrl($event->arguments(0), $event->arguments(1));
		});
	}
}
