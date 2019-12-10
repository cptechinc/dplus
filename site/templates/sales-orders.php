<?php
	$filter_salesorders = $modules->get('FilterSalesOrders');
	$filter_salesorders->init_query($user);
	$filter_salesorders->filter_query($input);
	$filter_salesorders->apply_sortby($page);
	$query = $filter_salesorders->get_query();
	$orders = $query->paginate($input->pageNum, 10);

	$page->body = $config->twig->render('sales-orders/search-form.twig', ['page' => $page, 'input' => $input]);
	$page->body .= $config->twig->render('sales-orders/sales-orders-list-links.twig', ['page' => $page, 'orders' => $orders, 'orderpage' => $pages->get('pw_template=sales-order-view')->url]);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'pagenbr' => $input->pageNum, 'resultscount'=> $orders->getNbResults()]);
	include __DIR__ . "/basic-page.php";
