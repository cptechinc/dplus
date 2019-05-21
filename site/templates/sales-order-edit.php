<?php
	if ($input->get->ordn) {
		$ordn = $input->get->text('ordn');
		$document_management = $modules->get('DocumentManagement');

		if (SalesOrderQuery::create()->filterByOehdnbr($ordn)->count()) {
			$order_query = SalesOrderQuery::create();
			$order_items_query = SalesOrderDetailQuery::create();
			$order = $order_query->findOneByOehdnbr($ordn);
			$order_items = $order_items_query->filterByOehdnbr($ordn)->find();
			$page->title = "Sales Order #$ordn";
			$page->listpage = $pages->get('pw_template=sales-orders');
			$page->body =  $config->twig->render('sales-orders/sales-order/sales-order-page.twig', ['page' => $page, 'order' => $order, 'order_items' => $order_items, 'document_management' => $document_management]);
		} elseif (SalesHistoryQuery::create()->filterByOehhnbr($ordn)->count()) {
			$page->headline = $page->title = "Sales Order #$ordn is not editable";
			$page->body = $config->twig->render('util/error-page.twig', ['msg' => "Sales Order #$ordn is in Sales History"]);
		} else {
			$page->headline = $page->title = "Sales Order #$ordn could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['msg' => "Check if the Order Number is correct or if it is in Sales History"]);
		}
	} else {
		$page->body = $config->twig->render('sales-orders/sales-order-lookup.twig', ['page' => $page]);
	}
	include __DIR__ . "/basic-page.php";
