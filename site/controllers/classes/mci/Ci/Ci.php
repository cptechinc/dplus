<?php namespace Controllers\Mci;

use Mvc\Controllers\AbstractController;
use ProcessWire\Page, ProcessWire\CiLoadCustomerShipto;

use Dplus\CodeValidators\Mar as MarValidator;

class Ci extends AbstractController {
	public static function index($data) {
		$fields = ['custID|text', 'q|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$page = self::pw('page');

		if (empty($data->custID) === false) {
			return self::customer($data);
		}
		//return self::list($data);
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

		$pages   = self::pw('pages');
		$modules = self::pw('modules');


		$html = $modules->get('HtmlWriter');

		$modules->get('DpagesMci')->init_customer_hooks();
		$modules->get('DpagesMci')->init_cipage();
		$load_customer = $modules->get('CiLoadCustomerShipto');
		$load_customer->set_custID($data->custID);
		$customer = $load_customer->get_customer();
		$contacts = $load_customer->get_contacts();
		$sales_orders = $load_customer->get_salesorders();
		$sales_history = $load_customer->get_saleshistory();
		$quotes = $load_customer->get_quotes();
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

		$page->body .= self::customerUserActions($load_customer);
		$page->body .= $config->twig->render('customers/ci/customer/contacts-panel.twig', ['customer' => $customer, 'contacts' => $contacts, 'resultscount'=> $contacts->getNbResults()]);
		$page->body .= $config->twig->render('customers/ci/customer/sales-orders-panel.twig', ['customer' => $customer, 'orders' => $sales_orders, 'resultscount'=> $sales_orders->getNbResults(), 'orderpage' => $pages->get('pw_template=sales-order-view')->url, 'sales_orders_list' => $page->cust_salesordersURL($customer->id)]);
		$page->body .= $config->twig->render('customers/ci/customer/sales-history-panel.twig', ['customer' => $customer, 'orders' => $sales_history, 'resultscount'=> $sales_history->getNbResults(), 'orderpage' => $pages->get('pw_template=sales-order-view')->url, 'shipped_orders_list' => $page->cust_saleshistoryURL($customer->id)]);

		if ($user->has_function('mqo')) {
			$page->body .= $config->twig->render('customers/ci/customer/quotes-panel.twig', ['customer' => $customer, 'quotes' => $quotes, 'resultscount'=> $quotes->getNbResults(), 'quotepage' => $pages->get('pw_template=quote-view')->url, 'quotes_list' => $page->cust_quotesURL($customer->id)]);
		}
		$config->scripts->append(hash_templatefile('scripts/customer/ci-customer.js'));
	}

	private static function customerUserActions(CiLoadCustomerShipto $loader) {
		$modules = self::pw('modules');
		$config  = self::pw('config');
		$filter = $modules->get('FilterUserActions');
		$module_useractions = $modules->get('FilterUserActions');
		$query = $filter->get_actionsquery(self::pw('input'));
		$query->filterByStatusIncomplete();
		$query->filterByCustomerlink($loader->get_custID());
		$actions = $query->paginate(1, 10);
		return $config->twig->render('customers/ci/customer/actions-panel.twig', ['module_useractions' => $module_useractions, 'actions' => $actions, 'resultscount'=> $actions->getNbResults()]);
	}

	private static function customerContacts() {
		
	}
}
