<?php
	use Map\CustomerTableMap;
	use Propel\Runtime\ActiveQuery\Criteria;

	$query = CustomerQuery::create();

	if ($input->get->custID) {
		$custID = $input->get->text('custID');
		$customer = $query->findOneByCustid($custID);
		$page->title = "CI: $customer->name";
		$page->body  = $config->twig->render('customers/customer-page.twig', ['page' => $page, 'customer' => $customer]);
	} else {
		if ($input->get->q) {
			$q = $input->get->text('q');
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
