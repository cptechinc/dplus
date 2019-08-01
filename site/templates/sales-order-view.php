<?php
	$config_salesorders = $modules->get('SalesOrdersConfig');
	$modules->get('DplusoPagesSalesOrder')->init_salesorder_hooks();
	$http = new ProcessWire\WireHttp();

	if ($input->get->ordn) {
		$ordn = $input->get->text('ordn');
		$document_management = $modules->get('DocumentManagement');

		$http->get($pages->get('template=redir, redir_file=sales-order')->httpUrl."?action=get-order-notes&ordn=$ordn&sessionID=".session_id());

		if (SalesOrderQuery::create()->filterByOehdnbr($ordn)->count()) {
			$notes = QnoteQuery::create()->filterBySessionidOrdernbrLinenbr(session_id(), $ordn, $linenbr = 0);

			$order_query = SalesOrderQuery::create();
			$order_items_query = SalesOrderDetailQuery::create();
			$order = $order_query->findOneByOehdnbr($ordn);
			$can_editorder = $user->can_editorder($order);
			$order_items = $order_items_query->filterByOehdnbr($ordn)->find();
			$customer = CustomerQuery::create()->findOneByCustid($order->custid);
			$page->title = "Sales Order #$ordn";
			$page->listpage = $pages->get('pw_template=sales-orders');
			$page->formurl = $pages->get('template=dplus-menu')->child('template=redir')->url;
			$page->body =  $config->twig->render('sales-orders/sales-order/sales-order-page.twig', ['page' => $page, 'customer' => $customer, 'order' => $order, 'order_items' => $order_items, 'user' => $user, 'document_management' => $document_management, 'notes' => $notes]);

			$shipments = SalesOrderShipmentQuery::create()->findByOrderNumber($ordn);
			$urlmaker = $modules->get('DplusURLs');
			$page->body .= $config->twig->render('sales-orders/sales-order/sales-order-tracking.twig', ['page' => $page, 'order' => $order, 'shipments' => $shipments, 'urlmaker' => $urlmaker]);

			$documents = $document_management->get_salesorderdocuments($ordn);
			$page->body .= $config->twig->render('sales-orders/sales-order/documents.twig', ['page' => $page, 'documents' => $documents, 'document_management' => $document_management, 'ordn' => $ordn]);

			// TODO: NOTES
			$notes = QnoteQuery::create()->filterBySessionidOrdernbrLinenbr(session_id(), $ordn, $linenbr = 0);
			$page->body .= $config->twig->render('sales-orders/sales-order/sales-order-notes.twig', ['page' => $page, 'notes' => $notes, 'ordn' => $ordn]);

			$page->body .= $config->twig->render('sales-orders/sales-order/sales-order-actions.twig', ['page' => $page, 'order' => $order, 'shipments' => $shipments]);
		} elseif (SalesHistoryQuery::create()->filterByOehhnbr($ordn)->count()) {
			$order_query = SalesHistoryQuery::create();
			$order = $order_query->findOneByOehhnbr($ordn);
			$customer = CustomerQuery::create()->findOneByCustid($order->custid);
			$order_items_query = SalesHistoryDetailQuery::create();
			$order_items = $order_items_query->filterByOehhnbr($ordn)->find();
			$page->title = "Sales Order #$ordn";
			$page->listpage = $pages->get('pw_template=sales-history-orders');
			$page->body = $config->twig->render('sales-orders/sales-history/sales-history-page.twig', ['page' => $page, 'order' => $order, 'customer' => $customer, 'order_items' => $order_items,  'document_management' => $document_management]);

			$shipments = SalesOrderShipmentQuery::create()->findByOrderNumber($ordn);
			$urlmaker = $modules->get('DplusURLs');
			$page->body .= $config->twig->render('sales-orders/sales-order/sales-order-tracking.twig', ['page' => $page, 'order' => $order, 'shipments' => $shipments, 'urlmaker' => $urlmaker]);

			$documents = $document_management->get_salesorderdocuments($ordn);
			$page->body .= $config->twig->render('sales-orders/sales-order/sales-order-notes.twig', ['page' => $page, 'documents' => $documents, 'document_management' => $document_management, 'ordn' => $ordn]);

			// TODO: NOTES
			$notes = QnoteQuery::create()->filterBySessionidOrdernbrLinenbr(session_id(), $ordn, $linenbr = 0);
			$page->body .= $config->twig->render('sales-orders/sales-order/sales-order-notes.twig', ['page' => $page, 'notes' => $notes, 'ordn' => $ordn]);

		} else {
			$page->headline = $page->title = "Sales Order #$ordn could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['msg' => "Check if the Order Number is correct or if it is in Sales History"]);
		}
	} else {
		$page->body = $config->twig->render('sales-orders/sales-order-lookup.twig', ['page' => $page]);
	}
	include __DIR__ . "/basic-page.php";
