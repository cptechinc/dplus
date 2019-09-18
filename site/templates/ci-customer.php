<?php
	use Map\CustomerTableMap;
	use Propel\Runtime\ActiveQuery\Criteria;
	$module_useractions      = $modules->get('FilterUserActions');

	$html = $modules->get('HtmlWriter');

	if ($input->get->custID) {
		$custID = $input->get->text('custID');

		$query = CustomerQuery::create();
		$customer = $query->findOneByCustid($custID);

		$query = $module_useractions->get_actionsquery($input);
		$query->filterByStatusIncomplete();
		$query->filterByCustomerlink($custID);
		$actions = $query->paginate($input->pageNum, 10);

		$query = ContactQuery::create();
		$query->filterByArcucustid($custID);
		$contacts = $query->paginate($input->pageNum, 10);

		$query = SalesOrderQuery::create();
		$query->filterBycustid($custID);
		$orders = $query->paginate($input->pageNum, 10);

		$query = SalesHistoryQuery::create();
		$query->filterByArcucustid($custID);
		$shippedorders = $query->paginate($input->pageNum, 10);

		$page->title = "CI: $customer->name";

		$toolbar = $config->twig->render('customers/ci/toolbar.twig', ['page' => $page, 'customer' => $customer]);
		$header =  $config->twig->render('customers/ci/customer/header.twig', ['page' => $page, 'customer' => $customer]);

		$page->body = "<div class='row'>";
			$page->body .= $html->div('class=col-sm-2', $toolbar);
			$page->body .= $html->div('class=col-sm-10', $header);
		$page->body .= "</div>";

		$page->body .= $config->twig->render('shared/actions-panel.twig', ['page' => $page, 'actions' => $actions, 'resultscount'=> $actions->getNbResults()]);
		$page->body .= $config->twig->render('customers/ci/customer/contacts-panel.twig', ['page' => $page, 'customer' => $customer, 'contacts' => $contacts, 'resultscount'=> $contacts->getNbResults()]);
		$page->body .= $config->twig->render('customers/ci/customer/sales-orders-panel.twig', ['page' => $page, 'customer' => $customer, 'orders' => $orders, 'resultscount'=> $orders->getNbResults(), 'orderpage' => $pages->get('pw_template=sales-order-view')->url, 'sales_orders_list' => $pages->get('pw_template=sales-orders')->url]);
		$page->body .= $config->twig->render('customers/ci/customer/shipped-orders-panel.twig', ['page' => $page, 'customer' => $customer, 'orders' => $shippedorders, 'resultscount'=> $shippedorders->getNbResults()]);
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

		$customers = $query->paginate($input->pageNum, 10);

		$page->searchURL = $page->url;
		$page->body = $config->twig->render('customers/customer-search.twig', ['page' => $page, 'customers' => $customers]);
		$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $customers->getNbResults()]);
	}

	include __DIR__ . "/basic-page.php";
