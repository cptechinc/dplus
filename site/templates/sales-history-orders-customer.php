<?php
	$filter_saleshistory = $modules->get('FilterSalesHistory');
	$filter_saleshistory->init_query($user);
	$filter_saleshistory->filter_query($input);
	$query = $filter_saleshistory->get_query();
	$query->orderByDate_ordered('DESC');

	$custID = $input->get->text('custID');
	$load_customer = $modules->get('CiLoadCustomerShipto');
	$load_customer->set_custID($custID);
	$customer = $load_customer->get_customer();
	$page->title = "$customer->name Sales History";

	if ($input->get->shiptoID) {
		$shiptoID = $input->get->text('shiptoID');
		$load_customer->set_shiptoID($shiptoID);
		$shipto = $load_customer->get_shipto();
		$page->title = "$shipto->name Sales History";
	}

	$orders = $query->paginate($input->pageNum, 10);
	// TODO: Filter Form
	//$page->body = $config->twig->render('sales-orders/search-form.twig', ['page' => $page, 'input' => $input]);
	$page->body .= $config->twig->render('sales-orders/sales-history/customer/sales-history-list.twig', ['orders' => $orders, 'orderpage' => $pages->get('pw_template=sales-order-view')->url]);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'pagenbr' => $input->pageNum, 'resultscount'=> $orders->getNbResults()]);
	include __DIR__ . "/basic-page.php";
