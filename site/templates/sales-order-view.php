<?php
	$config_salesorders = $modules->get('SalesOrdersConfig');
	$modules->get('DplusoPagesSalesOrder')->init_salesorder_hooks();
	$module_useractions = $modules->get('FilterUserActions');
	$http = new ProcessWire\WireHttp();
	$html = $modules->get('HtmlWriter');

	if ($input->get->ordn) {
		$ordn = $input->get->text('ordn');
		
		if (SalesOrderQuery::create()->filterByOrdernumber($ordn)->count() || SalesHistoryQuery::create()->filterByOrdernumber($ordn)->count()) {
			$document_management = $modules->get('DocumentManagement');

			$type = 'order';
			if (SalesOrderQuery::create()->filterByOrdernumber($ordn)->count()) {
				$order = SalesOrderQuery::create()->findOneByOrdernumber($ordn);
				$order_items = SalesOrderDetailQuery::create()->filterByOrdernumber($ordn)->find();
				$page->listpage = $pages->get('pw_template=sales-orders');
				$notes = SalesOrderNotesQuery::create()->filterByOrdernumber($ordn)->filterByLine(0)->find();
			} elseif (SalesHistoryQuery::create()->filterByOrdernumber($ordn)->count()) {
				$type = 'history';
				$order = SalesHistoryQuery::create()->findOneByOrdernumber($ordn);
				$order_items = SalesHistoryDetailQuery::create()->filterByOrdernumber($ordn)->find();
				$page->listpage = $pages->get('pw_template=sales-history-orders');
				$notes = SalesHistoryNotesQuery::create()->filterByOrdernumber($ordn)->filterByLine(0)->find();
			}

			$customer = CustomerQuery::create()->findOneByCustid($order->custid);
			$page->title = "Sales Order #$ordn";
			$query_useractions = $module_useractions->get_actionsquery($input);
			$actions = $query_useractions->filterBySalesorderlink($ordn)->find();

			$page->body =  $config->twig->render("sales-orders/sales-$type/sales-$type-page.twig", ['page' => $page, 'customer' => $customer, 'order' => $order, 'order_items' => $order_items, 'user' => $user, 'document_management' => $document_management, 'notes' => $notes]);

			$shipments = SalesOrderShipmentQuery::create()->findByOrdernumber($ordn);
			$urlmaker = $modules->get('DplusURLs');
			$page->body .= $config->twig->render('sales-orders/sales-order/sales-order-tracking.twig', ['page' => $page, 'order' => $order, 'shipments' => $shipments, 'urlmaker' => $urlmaker]);
			$documents = $document_management->get_salesorderdocuments($ordn);
			$page->body .= $config->twig->render('sales-orders/sales-order/documents.twig', ['page' => $page, 'documents' => $documents, 'document_management' => $document_management, 'ordn' => $ordn]);
			$page->body .= $config->twig->render('sales-orders/sales-order/qnotes.twig', ['page' => $page, 'notes' => $notes, 'ordn' => $ordn]);
			$page->body .= $config->twig->render('sales-orders/sales-order/user-actions.twig', ['page' => $page, 'module_useractions' => $module_useractions, 'actions' => $actions, 'ordn' => $ordn]);

			if ($type == 'order') {
				$page->body .= $config->twig->render('sales-orders/sales-order/sales-order-actions.twig', ['page' => $page, 'order' => $order, 'shipments' => $shipments]);
			}
		} else {
			$page->headline = $page->title = "Sales Order #$ordn could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['msg' => "Check if the Order Number is correct or if it is in Sales History"]);

		}

	} else {
		$page->body = $config->twig->render('sales-orders/sales-order-lookup.twig', ['page' => $page]);
	}
	include __DIR__ . "/basic-page.php";
