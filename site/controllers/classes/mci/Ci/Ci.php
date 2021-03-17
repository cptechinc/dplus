<?php namespace Controllers\Mci;
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

class Ci extends AbstractController {
	public static function index($data) {
		$fields = ['custID|text', 'q|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');

		if (empty($data->custID) === false) {
			return self::customer($data);
		}
		return self::list($data);
	}

	public static function customer($data) {
		$page   = self::pw('page');
		$config = self::pw('config');
		$user = self::pw('user');
		$fields = ['custID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = new MarValidator();

		if ($validate->custid($data->custID) === false) {
			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Customer $data->custID not found"]);
			return $page->body;
		}

		if ($user->has_customer($data->custID) === false) {
			$page->searchURL = $page->url;
			$page->headline = "User $user->name Does Not Have Access to $data->custID";
			$page->body = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => $page->headline, 'iconclass' => 'fa fa-warning fa-2x', 'message' => "You do not have permission to access this customer"]);
			$page->body .= $html->div('class=mb-3');
			$page->body .= $config->twig->render('customers/search-form.twig', ['page' => $page]);
			return $page->body;
		}

		$pages = self::pw('pages');
		$modules = self::pw('modules');
		$html = $modules->get('HtmlWriter');

		$modules->get('DpagesMci')->init_customer_hooks();
		$modules->get('DpagesMci')->init_cipage();
		$customer = CustomerQuery::create()->findOneById($data->custID);
		$page->show_breadcrumbs = false;

		$page->body .= $config->twig->render('customers/ci/bread-crumbs.twig', ['customer' => $customer]);
		$page->headline = "CI: $customer->name";

		if (!$customer->is_active()) {
			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => 'Inactive Customer', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Customer $data->custID is not active"]);
			$page->body .= $html->div('class=mb-3');
		}

		if ($customer->has_credithold()) {
			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => 'Credit Hold', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Customer $data->custID has a credit hold"]);
			$page->body .= $html->div('class=mb-3');
		}

		$toolbar = $config->twig->render('customers/ci/customer/toolbar.twig', ['custID' => $customer->id]);
		$header  = $config->twig->render('customers/ci/customer/header.twig', ['customer' => $customer]);

		$page->body .= "<div class='row'>";
			$page->body .= $html->div('class=col-sm-2 pl-0', $toolbar);
			$page->body .= $html->div('class=col-sm-10', $header);
		$page->body .= "</div>";

		$page->body .= self::customerUserActions($customer);
		$page->body .= self::customerContacts($customer);
		$page->body .= self::customerSalesOrders($customer);
		$page->body .= self::customerSalesHistory($customer);
		$page->body .= self::customerQuotes($customer);
		$config->scripts->append(hash_templatefile('scripts/customer/ci-customer.js'));
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
		return $config->twig->render('customers/ci/customer/sales-orders-panel.twig', ['customer' => $customer, 'orders' => $orders, 'resultscount'=> $orders->getNbResults(), 'orderpage' => self::pw('pages')->get('pw_template=sales-order-view')->url, 'sales_orders_list' => $page->cust_salesordersURL($customer->id)]);
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
				self::pw('session')->redirect($page->url."?custID=$data->q", $http301 = false);
			}

			$filter->search($data->q);
			$page->headline = "CI: Searching for '$data->q'";
		}
		$customers = $filter->query->paginate(self::pw('input')->pageNum, 10);
		$config = self::pw('config');
		$page->searchURL = $page->url;
		$page->body = $config->twig->render('customers/customer-search.twig', ['customers' => $customers, 'datamatcher' => self::pw('modules')->get('RegexData'), 'q' => $data->q]);
		$page->body .= $config->twig->render('util/paginator.twig', ['resultscount'=> $customers->getNbResults()]);
	}
}
