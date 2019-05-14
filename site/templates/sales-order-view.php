<?php
	if ($input->get->ordn) {
		$ordn = $input->get->text('ordn');

		if (SalesOrderQuery::create()->filterByOehdnbr($ordn)->count()) {
			$order_query = SalesOrderQuery::create();
			$order_items_query = SalesOrderDetailQuery::create();
			$order = $order_query->findOneByOehdnbr($ordn);
			$order_items = $order_items_query->filterByOehdnbr($ordn)->find();
			$page->title = "Sales Order #$ordn";
			$page->listpage = $pages->get('pw_template=sales-orders');
			$page->body =  $config->twig->render('sales-orders/sales-order/sales-order-page.twig', ['page' => $page, 'order' => $order, 'order_items' => $order_items]);

			$shipments = SalesOrderShipmentQuery::create()->findByOrderNumber($ordn);
			$page->body .= $config->twig->render('sales-orders/sales-order/sales-order-tracking.twig', ['page' => $page, 'order' => $order, 'shipments' => $shipments]);

			$page->body .= $config->twig->render('sales-orders/sales-order/sales-order-actions.twig', ['page' => $page, 'order' => $order, 'shipments' => $shipments]);
		} elseif (SalesHistoryQuery::create()->filterByOehhnbr($ordn)->count()) {
			$order_query = SalesHistoryQuery::create();
			$order = $order_query->findOneByOehhnbr($ordn);
			$order_items_query = SalesHistoryDetailQuery::create();
			$order_items = $order_items_query->filterByOehhnbr($ordn)->find();
			$page->title = "Sales Order #$ordn";
			$page->listpage = $pages->get('pw_template=sales-history-orders');
			$page->body = $config->twig->render('sales-orders/sales-history/sales-history-page.twig', ['page' => $page, 'order' => $order, 'order_items' => $order_items]);

		} else {
			$page->headline = $page->title = "Sales Order #$ordn could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['msg' => "Check if the Order Number is correct or if it is in Sales History"]);
		}
	} else {
		$page->body = $config->twig->render('sales-orders/sales-order-lookup.twig', ['page' => $page]);
	}
	include __DIR__ . "/basic-page.php";
