<?php
	$query = SalesHistoryQuery::create();
	$orders = $query->limit(10)->orderByOehhordrdate('DESC')->find();

	$page->body = $config->twig->render('sales-orders/sales-history/sales-history-list.twig', ['orders' => $orders, 'orderpage' => $pages->get('pw_template=sales-order-view')->url]);
	include __DIR__ . "/basic-page.php";