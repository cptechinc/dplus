<?php
	use Map\CustomerTableMap;
	use Propel\Runtime\ActiveQuery\Criteria;

	$module_useractions = $modules->get('FilterUserActions');
	$html = $modules->get('HtmlWriter');

	if ($input->get->custID) {
		$custID = $input->get->text('custID');

		if ($user->has_customer($custID)) {
			$modules->get('MciPages')->init_customer_hooks();
			$load_customer = $modules->get('CiLoadCustomerShipto');

			$load_customer->set_custID($custID);

			// TODO VALIDATION
			if ($load_customer->customer_exists()) {
				$customer = $load_customer->get_customer();
				$actions = $load_customer->get_useractions($input);
				$contacts = $load_customer->get_contacts();
				$sales_orders = $load_customer->get_salesorders();
				$sales_history = $load_customer->get_saleshistory();
				$page->title = "CI: $customer->name";
				$toolbar = $config->twig->render('customers/ci/customer/toolbar.twig', ['page' => $page, 'custID' => $customer->id]);

				$header =  $config->twig->render('customers/ci/customer/header.twig', ['page' => $page, 'customer' => $customer]);

				$page->body = "<div class='row'>";
					$page->body .= $html->div('class=col-sm-2', $toolbar);
					$page->body .= $html->div('class=col-sm-10', $header);
				$page->body .= "</div>";
				$page->body .= $config->twig->render('customers/ci/customer/actions-panel.twig', ['page' => $page, 'module_useractions' => $module_useractions, 'actions' => $actions, 'resultscount'=> $actions->getNbResults()]);
				$page->body .= $config->twig->render('customers/ci/customer/contacts-panel.twig', ['page' => $page, 'customer' => $customer, 'contacts' => $contacts, 'resultscount'=> $contacts->getNbResults()]);
				$page->body .= $config->twig->render('customers/ci/customer/sales-orders-panel.twig', ['page' => $page, 'customer' => $customer, 'orders' => $sales_orders, 'resultscount'=> $sales_orders->getNbResults(), 'orderpage' => $pages->get('pw_template=sales-order-view')->url, 'sales_orders_list' => $page->cust_salesordersURL($customer->id)]);
				$page->body .= $config->twig->render('customers/ci/customer/shipped-orders-panel.twig', ['page' => $page, 'customer' => $customer, 'orders' => $sales_history, 'resultscount'=> $sales_history->getNbResults(), 'orderpage' => $pages->get('pw_template=sales-order-view')->url, 'shipped_orders_list' => $page->cust_saleshistoryURL($customer->id)]);
				$config->scripts->append(hash_templatefile('scripts/customer/ci-customer.js'));
			} else {
				$page->searchURL = $page->url;
				$page->title = "Customer $custID Not Found";
				$page->body = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => $page->title, 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Check the Customer ID is correct"]);
				$page->body .= $html->div('class=mb-3');
				$page->body .= $config->twig->render('customers/search-form.twig', ['page' => $page]);
			}
		} else {
			$page->searchURL = $page->url;
			$page->title = "User $user->name Does Not Have Access to $custID";
			$page->body = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => $page->title, 'iconclass' => 'fa fa-warning fa-2x', 'message' => "User does not have permission to access this customer"]);
			$page->body .= $html->div('class=mb-3');
			$page->body .= $config->twig->render('customers/search-form.twig', ['page' => $page]);
		}
	} else {
		$query = CustomerQuery::create();
		$exact_query = CustomerQuery::create();

		if ($input->get->q) {
			$q = strtoupper($input->get->text('q'));

			if ($exact_query->filterByCustid($q)->count() == 1) {
				$session->redirect($page->url."?custID=$q");
			}

			$page->title = "CI: Searching for '$q'";
			$col_custid = Customer::get_aliasproperty('custid');
			$col_name = Customer::get_aliasproperty('name');
			$columns = array($col_custid, $col_name);
			$query->search_filter($columns, strtoupper($q));
		}

		if ($page->has_orderby()) {
			$orderbycolumn = $page->orderby_column;
			$sort = $page->orderby_sort;
			$tablecolumn = Customer::get_aliasproperty($orderbycolumn);
			$query->sortBy($tablecolumn, $sort);
		}

		if ($user->is_salesrep()) {
			$query->filterByCustid($user->get_customers(), Criteria::IN);
		}

		$customers = $query->paginate($input->pageNum, 10);

		$page->searchURL = $page->url;
		$page->body = $config->twig->render('customers/customer-search.twig', ['page' => $page, 'customers' => $customers]);
		$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $customers->getNbResults()]);
	}

	include __DIR__ . "/basic-page.php";
