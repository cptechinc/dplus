<?php
	$salesorder_config = $modules->get('SalesOrdersConfig');
	
	if ($input->get->ordn) {
		$ordn = $input->get->text('ordn');
		$document_management = $modules->get('DocumentManagement');

		if (SalesOrderQuery::create()->filterByOehdnbr($ordn)->count()) {
			$order_query = SalesOrderQuery::create();
			$order_items_query = SalesOrderDetailQuery::create();
			$order = $order_query->findOneByOehdnbr($ordn);
			$order_items = $order_items_query->filterByOehdnbr($ordn)->find();
			$customer = CustomerQuery::create()->findOneByCustid($order->custid);
			$page->title = "Sales Order #$ordn";
			$page->listpage = $pages->get('pw_template=sales-orders');
			$page->body =  $config->twig->render('sales-orders/sales-order/sales-order-page.twig', ['page' => $page, 'order' => $order, 'order_items' => $order_items, 'document_management' => $document_management]);

			$shipments = SalesOrderShipmentQuery::create()->findByOrderNumber($ordn);
			$urlmaker = $modules->get('DplusURLs');
			$page->body .= $config->twig->render('sales-orders/sales-order/sales-order-tracking.twig', ['page' => $page, 'order' => $order, 'shipments' => $shipments, 'urlmaker' => $urlmaker]);

			$documents = $document_management->get_salesorderdocuments($ordn);
			$page->body .= $config->twig->render('sales-orders/sales-order/documents.twig', ['page' => $page, 'documents' => $documents, 'document_management' => $document_management, 'ordn' => $ordn]);

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
