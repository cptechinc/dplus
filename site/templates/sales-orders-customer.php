<?php
	$filter = new Dplus\Filters\Mso\SalesOrder();
	$filter->user($user);
	$filter->filterInput($input);
	$filter->sortby($page);

	$load_customer = $modules->get('CiLoadCustomerShipto');
	$load_customer->set_custID($input->get->text('custID'));
	$customer = $load_customer->get_customer();
	$page->title = "$customer->name Sales Orders";

	if ($input->get->shiptoID) {
		$load_customer->set_shiptoID($input->get->text('shiptoID'));
		$shipto = $load_customer->get_shipto();
		$page->title = "$shipto->name Sales Orders";
	}

	$orders = $filter->query->paginate($input->pageNum, 10);
	$page->body = $config->twig->render('sales-orders/customer/search-form.twig', ['page' => $page, 'input' => $input, 'custID' => $load_customer->get_custID(), 'shiptoID' => $load_customer->get_shiptoID()]);
	$page->body .= $config->twig->render('sales-orders/customer/sales-order-list-links.twig', ['page' => $page, 'orders' => $orders, 'orderpage' => $pages->get('pw_template=sales-order-view')->url]);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'pagenbr' => $input->pageNum, 'resultscount'=> $orders->getNbResults()]);
	include __DIR__ . "/basic-page.php";
