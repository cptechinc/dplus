<?php
	$config_salesorders = $modules->get('SalesOrdersConfig');
	$modules->get('DplusoPagesSalesOrder')->init_salesorder_hooks();
	$http = new ProcessWire\WireHttp();

	if ($input->get->ordn) {
		$ordn = $input->get->text('ordn');

		if (SalesOrderQuery::create()->filterByOehdnbr($ordn)->count()) {
			$order_query = SalesOrderQuery::create();
			$order_items_query = SalesOrderDetailQuery::create();
			$order = $order_query->findOneByOehdnbr($ordn);
			$is_orderlocked = $user->is_editingorder($order);
			$order_items = $order_items_query->filterByOehdnbr($ordn)->find();
			$customer = CustomerQuery::create()->findOneByCustid($order->custid);
			$page->title = "Editing Sales Order #$ordn";
			$page->listpage = $pages->get('pw_template=sales-orders');
			$page->formurl = $pages->get('template=dplus-menu')->child('template=redir')->url;
			$page->body =  $config->twig->render('sales-orders/sales-order/edit-sales-order-page.twig', ['page' => $page, 'customer' => $customer, 'order' => $order, 'order_items' => $order_items, 'is_orderlocked' => $is_orderlocked]);
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
