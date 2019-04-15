<?php
	$query = SalesOrderQuery::create();
	$orders = $query->limit(10)->filterbySalesPerson('RDB')->orderByOehdordrdate('DESC')->find();

	$page->body = $config->twig->render('sales-orders/sales-orders-list.twig', ['orders' => $orders, 'orderpage' => $pages->get('pw_template=sales-order-view')->url]);
	include __DIR__ . "/basic-page.php";