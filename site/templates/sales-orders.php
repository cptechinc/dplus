<?php
	$query = SalesOrderQuery::create();
	$query->filterbySalesPerson('RDB');
	$query->orderByOehdordrdate('DESC');
	$orders = $query->paginate($input->pageNum, 10);

	$page->body = $config->twig->render('sales-orders/search-form.twig', []);
	$page->body .= $config->twig->render('sales-orders/sales-orders-list.twig', ['orders' => $orders, 'orderpage' => $pages->get('pw_template=sales-order-view')->url]);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'pagenbr' => $input->pageNum, 'resultscount'=> $orders->getNbResults()]);
	include __DIR__ . "/basic-page.php";
