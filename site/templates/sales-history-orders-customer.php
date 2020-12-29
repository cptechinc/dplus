<?php
	$filter_saleshistory = $modules->get('FilterSalesHistory');
	$filter_saleshistory->init_query($user);
	$filter_saleshistory->filter_input($input);
	$filter_saleshistory->apply_sortby($page);
	$query = $filter_saleshistory->get_query();

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
	$page->body = $config->twig->render('sales-orders/sales-history/customer/search-form.twig', ['page' => $page, 'input' => $input, 'custID' => $load_customer->get_custID(), 'shiptoID' => $load_customer->get_shiptoID()]);
	$page->body .= $config->twig->render('sales-orders/sales-history/customer/sales-history-list-links.twig', ['page' => $page, 'orders' => $orders, 'orderpage' => $pages->get('pw_template=sales-order-view')->url]);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'pagenbr' => $input->pageNum, 'resultscount'=> $orders->getNbResults()]);
	include __DIR__ . "/basic-page.php";
