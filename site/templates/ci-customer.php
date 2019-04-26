<?php
	use Map\CustomerTableMap;
	use Propel\Runtime\ActiveQuery\Criteria;

	if ($input->get->custID) {
		$custID = $input->get->text('custID');

		$query = CustomerQuery::create();
		$customer = $query->findOneByCustid($custID);

		$query = ContactQuery::create();
		$contacts = $query->filterByArcucustid($custID);

		$query = UseractionsQuery::create();
		$actions = $query->groupByCustomerlink($custID);

		$query = SalesOrderQuery::create();
		$orders = $query->filterByArcucustid($custID);

		$query = SalesHistoryQuery::create();
		$shippedorders = $query->filterByArcucustid($custID);

		$page->title = "CI: $customer->name";
		$page->body  = $config->twig->render('customers/customer-page.twig', ['page' => $page, 'customer' => $customer]);
		$page->body  .= $config->twig->render('customers/ci-customer/customer-contacts.twig', ['page' => $page, 'customer' => $customer, 'contacts' => $contacts]);
		$page->body  .= $config->twig->render('customers/ci-customer/customer-actions.twig', ['page' => $page, 'customer' => $customer, 'actions' => $actions]);
		$page->body  .= $config->twig->render('customers/ci-customer/customer-sales-orders.twig', ['page' => $page, 'customer' => $customer, 'orders' => $orders]);
		$page->body  .= $config->twig->render('customers/ci-customer/customer-shipped-orders.twig', ['page' => $page, 'customer' => $customer, 'orders' => $shippedorders]);
	} else {
		if ($input->get->q) {
			$q = $input->get->text('q');
			$page->title = "CI: Searching for '$q'";
			$col_custid = Customer::get_aliasproperty('custid');
			$col_name = Customer::get_aliasproperty('name');
			$columns = array($col_custid, $col_name);
			$query->search_filter($columns, $q);
		}
		$customers = $query->paginate($input->pageNum, 10);
		$page->searchURL = $page->url;
		$page->body = $config->twig->render('customers/customer-search.twig', ['page' => $page, 'customers' => $customers]);
		$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'pagenbr' => $input->pageNum, 'resultscount'=> $customers->getNbResults()]);
	}

	include __DIR__ . "/basic-page.php";
