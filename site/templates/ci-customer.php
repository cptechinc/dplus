<?php
	use Map\CustomerTableMap;
	use Propel\Runtime\ActiveQuery\Criteria;

	$module_useractions = $modules->get('FilterUserActions');
	$html = $modules->get('HtmlWriter');
	$lookup_customer = $modules->get('LookupCustomer');

	if ($input->get->custID) {
		$custID = $input->get->text('custID');

		// TODO VALIDATION
		if ($lookup_customer->lookup_customer($custID)) {
			if ($user->has_customer($custID)) {
				$modules->get('DpagesMci')->init_customer_hooks();
				$modules->get('DpagesMci')->init_cipage();
				$load_customer = $modules->get('CiLoadCustomerShipto');
				$load_customer->set_custID($custID);
				$customer = $load_customer->get_customer();
				$actions = $load_customer->get_useractions($input);
				$contacts = $load_customer->get_contacts();
				$sales_orders = $load_customer->get_salesorders();
				$sales_history = $load_customer->get_saleshistory();
				$quotes = $load_customer->get_quotes();
				$page->show_breadcrumbs = false;
				$page->body .= $config->twig->render('customers/ci/bread-crumbs.twig', ['page' => $page, 'customer' => $customer]);
				$page->headline = "CI: $customer->name";

				if (!$customer->is_active()) {
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => 'Inactive Customer', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Customer $custID is not active"]);
					$page->body .= $html->div('class=mb-3');
				}

				$toolbar = $config->twig->render('customers/ci/customer/toolbar.twig', ['page' => $page, 'custID' => $customer->id]);
				$header =  $config->twig->render('customers/ci/customer/header.twig', ['page' => $page, 'customer' => $customer]);

				$page->body .= "<div class='row'>";
					$page->body .= $html->div('class=col-sm-2 pl-0', $toolbar);
					$page->body .= $html->div('class=col-sm-10', $header);
				$page->body .= "</div>";
				$page->body .= $config->twig->render('customers/ci/customer/actions-panel.twig', ['page' => $page, 'module_useractions' => $module_useractions, 'actions' => $actions, 'resultscount'=> $actions->getNbResults()]);
				$page->body .= $config->twig->render('customers/ci/customer/contacts-panel.twig', ['page' => $page, 'customer' => $customer, 'contacts' => $contacts, 'resultscount'=> $contacts->getNbResults()]);
				$page->body .= $config->twig->render('customers/ci/customer/sales-orders-panel.twig', ['page' => $page, 'customer' => $customer, 'orders' => $sales_orders, 'resultscount'=> $sales_orders->getNbResults(), 'orderpage' => $pages->get('pw_template=sales-order-view')->url, 'sales_orders_list' => $page->cust_salesordersURL($customer->id)]);
				$page->body .= $config->twig->render('customers/ci/customer/sales-history-panel.twig', ['page' => $page, 'customer' => $customer, 'orders' => $sales_history, 'resultscount'=> $sales_history->getNbResults(), 'orderpage' => $pages->get('pw_template=sales-order-view')->url, 'shipped_orders_list' => $page->cust_saleshistoryURL($customer->id)]);

				if ($user->has_function('mqo')) {
					$page->body .= $config->twig->render('customers/ci/customer/quotes-panel.twig', ['page' => $page, 'customer' => $customer, 'quotes' => $quotes, 'resultscount'=> $quotes->getNbResults(), 'quotepage' => $pages->get('pw_template=quote-view')->url, 'quotes_list' => $page->cust_quotesURL($customer->id)]);
				}
				$config->scripts->append(hash_templatefile('scripts/customer/ci-customer.js'));
			} else {
				$page->searchURL = $page->url;
				$page->headline = "User $user->name Does Not Have Access to $custID";
				$page->body = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => $page->headline, 'iconclass' => 'fa fa-warning fa-2x', 'message' => "You doe not have permission to access this customer"]);
				$page->body .= $html->div('class=mb-3');
				$page->body .= $config->twig->render('customers/search-form.twig', ['page' => $page]);
			}
		} else {
			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Customer $custID not found"]);
		}
	} else {
		$filter_customers = $modules->get('FilterCustomers');
		$filter_customers->init_query($user);
		$filter_customers->filter_search($input->get->text('q'));

		if ($input->get->q) {
			$q = strtoupper($input->get->text('q'));
			$lookup_customer->lookup_customer($q);

			if ($lookup_customer->exists) {
				$session->redirect($page->url."?custID=$q");
			}
			$filter_customers->filter_search($q);
			$page->headline = "CI: Searching for '$q'";
		}

		$filter_customers->apply_sortby($page);
		$query = $filter_customers->get_query();
		$customers = $query->paginate($input->pageNum, 10);

		$page->searchURL = $page->url;
		$page->body = $config->twig->render('customers/customer-search.twig', ['page' => $page, 'customers' => $customers, 'datamatcher' => $modules->get('RegexData'), 'q' => $input->get->text('q')]);
		$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $customers->getNbResults()]);
	}

	include __DIR__ . "/basic-page.php";
